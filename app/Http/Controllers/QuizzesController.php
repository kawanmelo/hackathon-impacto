<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateQuizRequest;
use App\Http\Requests\SubmitQuizRequest;
use App\Models\Alternative;
use App\Models\Discipline;
use App\Models\Group;
use App\Models\Question;
use App\Models\QuestionResult;
use App\Models\Quiz;
use App\Models\Student;
use App\Services\OpenAIService;
use App\Services\QuizService;
use Illuminate\Http\JsonResponse;

class QuizzesController extends Controller
{

    public function __construct(
        private readonly QuizService $quizService,
        private readonly OpenAIService $openAIService,
    ) {
    }

    public function getQuiz(Quiz $quiz): JsonResponse
    {
        $quiz->load('questions.alternatives');
        return response()->json($quiz);
    }

    public function listAllQuestionsByDiscipline(Discipline $discipline): JsonResponse
    {
        return response()->json(Question::query()->where('discipline_id', $discipline->id)->get()->load('alternatives'));
    }

    public function create(CreateQuizRequest $request): JsonResponse
    {
        $quiz = $this->quizService->create(
            $request->safe()->only([
                'title',
                'description',
                'discipline_id'
            ]));
        $this->quizService->attachQuestions($quiz, $request->validated()['questions']);
        $quiz->load('questions');
        return response()->json($quiz);
    }

    public function submit(SubmitQuizRequest $request): JsonResponse
    {
        $studentId = $request->validated()['student_id'];
        $quizId = $request->validated()['quiz_id'];
        $answers = $request->validated()['answers'];

        $quiz = Quiz::with('questions.alternatives')->findOrFail($quizId);

        $correctCount = 0;
        $total = count($answers);

        foreach ($answers as $answer) {
            $questionId = $answer['question_id'];
            $alternativeId = $answer['alternative_id'];

            $alternative = Alternative::query()->find($alternativeId);
            $isCorrect = (bool) ($alternative->is_correct ?? false);

            if ($isCorrect) {
                $correctCount++;
            }

            QuestionResult::query()->updateOrCreate(
                [
                    'student_id' => $studentId,
                    'question_id' => $questionId,
                    'quiz_id' => $quizId,
                ],
                [
                    'score' => $isCorrect,
                    'time_spent' => $answer['time_spent'] ?? null,
                ]
            );
        }

        $scorePercent = (int)(($correctCount / $total) * 100);

        return response()->json([
            'quiz_id' => $quizId,
            'student_id' => $studentId,
            'correct_answers' => $correctCount,
            'total_questions' => $total,
            'score_percent' => $scorePercent,
            'success_rate' => "{$scorePercent}%",
            'message' => $scorePercent >= 70 ? 'Parabéns, ótimo desempenho!' : 'Continue praticando!',
        ]);
    }

    public function getResultsByQuiz(Quiz $quiz, Student $student): JsonResponse
    {
        return response()->json($quiz->results()->where('student_id', $student->id)->get());
    }

    public function generateGroupReport(Group $group): JsonResponse
    {
        return response()->json($this->openAIService->generateQuizReportForGroup($group->id));
    }

}
