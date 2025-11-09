<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitQuizRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'quiz_id' => ['required', 'exists:quizzes,id'],
            'student_id' => ['required', 'exists:students,id'],
            'answers' => ['required', 'array'],
            'answers.*.question_id' => ['required', 'exists:questions,id'],
            'answers.*.alternative_id' => ['required', 'exists:alternatives,id'],
            'answers.*.time_spent' => ['nullable', 'numeric'],
        ];
    }
}
