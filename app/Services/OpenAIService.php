<?php

namespace App\Services;

use App\Clients\OpenAIClient;
use App\Enums\ApiStatus;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\Student;
use App\Models\StudentMetrics;
use App\Models\StudentProfile;
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

        $alunos = Student::with(['metricas', 'perfil'])
            ->where('group_id', $turmaId)
            ->get();

        $turmaMetricas = GroupMetrics::where('group_id', $turmaId)
            ->where('created_at', '>=', $dataInicio)
            ->get();

        $alunoMetricas = StudentMetrics::whereIn('student_id', $alunos->pluck('id'))
            ->where('created_at', '>=', $dataInicio)
            ->get();

        $resultados = QuestionResult::with(['aluno', 'desafio.disciplina'])
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
            'turma' => $turma,
            'turma_metricas' => $turmaMetricas,
            'alunos' => $alunos,
            'aluno_metricas' => $alunoMetricas,
            'disciplinas' => $disciplinas,
            'resultados_desafios' => $resultados,
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
        $startDate = Carbon::now()->subDays(7);

        $group = Group::with('students')->findOrFail($groupId);

        $results = QuestionResult::with(['question', 'student'])
            ->whereHas('student', fn($q) => $q->where('group_id', $groupId))
            ->where('created_at', '>=', $startDate)
            ->get();

        $groupedByQuiz = $results->groupBy('quiz_id')->map(function ($items, $quizId) {
            $quiz = Quiz::find($quizId);
            $total = $items->count();
            $correct = $items->where('score', true)->count();

            return [
                'quiz_id' => $quizId,
                'title' => $quiz->title,
                'accuracy_rate' => round(($correct / $total) * 100, 1),
                'most_missed_questions' => $items
                    ->where('score', false)
                    ->groupBy('question_id')
                    ->map->count()
                    ->sortDesc()
                    ->take(5)
                    ->mapWithKeys(fn($count, $qid) => [
                            Question::find($qid)->statement ?? 'Unknown question' => $count
                    ]),
            ];
        });

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
            'quiz_performance' => $groupedByQuiz,
        ];

        $prompt = config('prompts.generate_group_report');

        $response = $this->client->generateChatResponse(
            'gpt-5-nano',
            $this->buildInput($payload),
            $prompt,
            true
        );

        return new OperationResult(
            ApiStatus::Success->value,
            Response::HTTP_OK,
            'Group quiz report generated successfully.',
            [$this->concatResponse($response)]
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
