<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateQuizRequest extends FormRequest
{


    public function rules(): array
    {
        return [
            'discipline_id' => ['required','integer', 'exists:disciplines,id'],
            'title' => ['required', 'string'],
            'description' => ['required', 'string'],
            'questions' => ['required', 'array'],
            'questions.*' => ['integer', 'exists:questions,id'],
        ];
    }
}
