<?php

namespace App\Services;

use App\Models\Quiz;

class QuizService
{

    public function create(array $data): Quiz
    {
        return Quiz::query()->create($data);
    }

    public function attachQuestions(Quiz $quiz, array $questions): void
    {
        $quiz->questions()->sync($questions);
    }

}
