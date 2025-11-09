<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    protected $table = 'quizzes';
    protected $fillable = [
        'discipline_id',
        'title',
        'description',
    ];

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'question_quizzes', 'quiz_id', 'question_id');
    }

    public function results(): HasMany
    {
        return $this->hasMany(QuestionResult::class, 'quiz_id');
    }

}
