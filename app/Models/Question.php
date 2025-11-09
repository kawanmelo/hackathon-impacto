<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{

    protected $table = 'questions';

    protected $fillable = [
        'discipline_id',
        'content',
        'type',
        'difficulty',
    ];

    public function questionResults(): HasMany
    {
        return $this->hasMany(QuestionResult::class);
    }

    public function discipline(): BelongsTo
    {
        return $this->belongsTo(Discipline::class);
    }

    public function alternatives(): HasMany
    {
        return $this->hasMany(Alternative::class);
    }

    public function quizzes(): BelongsToMany
    {
        return $this->belongsToMany(Quiz::class, 'question_quizzes');
    }


}
