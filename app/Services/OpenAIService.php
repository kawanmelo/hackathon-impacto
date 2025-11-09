<?php

namespace App\Services;

use App\Clients\OpenAIClient;
use App\Enums\ApiStatus;
use App\Models\Student;
use App\Models\StudentMetrics;
use App\Models\Discipline;
use App\Models\QuestionResult;
use App\Models\Group;
use App\Models\GroupMetrics;
use App\Utils\OperationResult;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

readonly class OpenAIService
{

    public function __construct(private OpenAIClient $client)
    {
    }

    public function generateReportForTurma(int $turmaId): OperationResult
    {
        $dataInicio = Carbon::now()->subDays(7);

        $turma = Group::with('metrics')->findOrFail($turmaId);

        $alunos = Student::with(['metrics'])
            ->where('group_id', $turmaId)
            ->get();

        $turmaMetricas = GroupMetrics::where('group_id', $turmaId)
            ->where('created_at', '>=', $dataInicio)
            ->get();

        $alunoMetricas = StudentMetrics::whereIn('student_id', $alunos->pluck('id'))
            ->where('created_at', '>=', $dataInicio)
            ->get();

        $resultados = QuestionResult::with(['student', 'question.discipline'])
            ->whereHas('student', fn($q) => $q->where('group_id', $turmaId))
            ->where('created_at', '>=', $dataInicio)
            ->get();

        $disciplinas = Discipline::with(['questions' => function ($q) use ($dataInicio) {
            $q->where('created_at', '>=', $dataInicio);
        }])->get();


        $payload = [
            'periodo' => [
                'inicio' => $dataInicio->toDateString(),
                'fim' => now()->toDateString(),
            ],
            'turma' => [
                'id' => $turma->id,
                'nome' => $turma->name,
                'total_alunos' => $alunos->count(),
            ],
            'metricas_turma' => [
                'media_acertos' => round($turmaMetricas->avg('accuracy_rate'), 1),
                'media_tempo' => round($turmaMetricas->avg('average_time_spent'), 1),
            ],
            'ranking_alunos' => $alunos->map(function ($a) use ($alunoMetricas) {
                $m = $alunoMetricas->where('student_id', $a->id);
                return [
                    'id' => $a->id,
                    'nome' => $a->name,
                    'acertos' => round($m->avg('accuracy_rate'), 1),
                    'tempo_medio' => round($m->avg('average_time_spent'), 1),
                ];
            })->sortByDesc('acertos')->values(),
            'disciplinas' => $disciplinas->map(fn($d) => [
                'id' => $d->id,
                'nome' => $d->name,
                'total_questoes' => $d->questions->count(),
            ]),
        ];



        $prompt = config('prompts.gerar-relatorio-semanal');

        $response = $this->client->generateChatResponse(
            'gpt-5-nano',
            $this->buildInput($payload),
            $prompt,
            true
        );

        return new OperationResult(
            ApiStatus::Success->value,
            Response::HTTP_OK,
            'Relatório gerado com sucesso.',
            [$this->concatResponse($response)]
        );
    }

    public function generateQuizReportForGroup(int $groupId): OperationResult
    {
        $startDate = now()->subDays(7);

        $group = Group::with('students')->findOrFail($groupId);

        $results = QuestionResult::with(['question:id,id', 'quiz:id,title', 'student:id,name'])
            ->whereHas('student', fn($q) => $q->where('group_id', $groupId))
            ->where('created_at', '>=', $startDate)
            ->get();

        if ($results->isEmpty()) {
            return new OperationResult(
                ApiStatus::Success->value,
                Response::HTTP_OK,
                'No quiz results found for this group in the selected period.',
                []
            );
        }

        $groupedByQuiz = $results->groupBy('quiz_id')->map(function ($items, $quizId) {
            $quiz = $items->first()->quiz;
            $total = $items->count();
            $correct = $items->where('score', true)->count();
            $averageTime = round($items->avg('time_spent'), 1);

            // Questões mais acertadas (top 5)
            $mostCorrect = $items
                ->where('score', true)
                ->groupBy('question_id')
                ->map(fn($set) => $set->count())
                ->sortDesc()
                ->take(5)
                ->mapWithKeys(fn($count, $qid) => [
                    'Questão #' . $qid => $count
                ]);

            $mostMissed = $items
                ->where('score', false)
                ->groupBy('question_id')
                ->map(fn($set) => $set->count())
                ->sortDesc()
                ->take(5)
                ->mapWithKeys(fn($count, $qid) => [
                    'Questão #' . $qid => $count
                ]);

            return [
                'quiz_id' => $quizId,
                'title' => $quiz?->title ?? 'Sem título',
                'accuracy_rate' => round(($correct / $total) * 100, 1),
                'average_time_spent' => $averageTime,
                'most_correct_questions' => $mostCorrect,
                'most_missed_questions' => $mostMissed,
            ];
        });

        $studentRanking = $results
            ->groupBy('student_id')
            ->map(function ($items, $studentId) {
                $student = $items->first()->student;
                $totalAnswered = $items->count();
                $totalCorrect = $items->where('score', true)->count();
                $accuracyRate = $totalAnswered > 0
                    ? round(($totalCorrect / $totalAnswered) * 100, 1)
                    : 0;

                return [
                    'student_id' => $studentId,
                    'name' => $student?->name ?? 'Sem nome',
                    'accuracy_rate' => $accuracyRate,
                    'total_answered' => $totalAnswered,
                    'total_correct' => $totalCorrect,
                ];
            })
            ->sortByDesc('accuracy_rate')
            ->values();

        $totalAnswers = $results->count();
        $totalCorrectAnswers = $results->where('score', true)->count();
        $groupAccuracyRate = round(($totalCorrectAnswers / $totalAnswers) * 100, 1);
        $averageTimeOverall = round($results->avg('time_spent'), 1);

        $questionStats = $results
            ->groupBy('question_id')
            ->map(fn($set) => [
                'id' => $set->first()->question_id,
                'total' => $set->count(),
                'correct' => $set->where('score', true)->count(),
                'accuracy' => round(($set->where('score', true)->count() / $set->count()) * 100, 1),
            ]);

        $hardestQuestion = $questionStats->sortBy('accuracy')->first();
        $easiestQuestion = $questionStats->sortByDesc('accuracy')->first();

        $quizStats = $groupedByQuiz->map(fn($quiz) => $quiz['accuracy_rate']);
        $bestQuizId = $quizStats->sortDesc()->keys()->first();
        $worstQuizId = $quizStats->sort()->keys()->first();

        $bestQuiz = $groupedByQuiz[$bestQuizId] ?? null;
        $worstQuiz = $groupedByQuiz[$worstQuizId] ?? null;

        $payload = [
            'period' => [
                'start' => $startDate->toDateString(),
                'end' => now()->toDateString(),
            ],
            'group' => [
                'id' => $group->id,
                'name' => $group->name,
                'total_students' => $group->students->count(),
            ],
            'group_metrics' => [
                'average_accuracy_rate' => $groupAccuracyRate,
                'average_time_spent' => $averageTimeOverall,
                'hardest_question' => $hardestQuestion
                    ? 'Questão #' . $hardestQuestion['id'] . ' (' . $hardestQuestion['accuracy'] . '% de acertos)'
                    : null,
                'easiest_question' => $easiestQuestion
                    ? 'Questão #' . $easiestQuestion['id'] . ' (' . $easiestQuestion['accuracy'] . '% de acertos)'
                    : null,
                'best_quiz' => $bestQuiz
                    ? $bestQuiz['title'] . ' (' . $bestQuiz['accuracy_rate'] . '%)'
                    : null,
                'worst_quiz' => $worstQuiz
                    ? $worstQuiz['title'] . ' (' . $worstQuiz['accuracy_rate'] . '%)'
                    : null,
            ],
            'quiz_metrics' => $groupedByQuiz,
            'student_ranking' => $studentRanking,
        ];

        return new OperationResult(
            ApiStatus::Success->value,
            Response::HTTP_OK,
            'Group performance metrics generated successfully.',
            $payload
        );
    }





    public function concatResponse(array $response): string
    {
        return collect($response['output'])
            ->where('type', 'message')
            ->flatMap(function ($message) {
                return collect($message['content'])
                    ->pluck('text');
            })
            ->implode(" ");
    }

    public function buildInput(array $data): array
    {
        return [
            [
                'role' => 'developer',
                'content' => [
                    [
                        'type' => 'input_text',
                        'text' => json_encode($data),
                    ]
                ]
            ]
        ];
    }

}
