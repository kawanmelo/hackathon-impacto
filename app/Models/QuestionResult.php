<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionResult extends Model
{

    protected $table = 'question_results';

    protected $fillable = [
        'student_id',
        'question_id',
        'quiz_id',
        'score',
        'time_spent',
    ];

    protected $casts = [
        'score' => 'bool',
        'time_spent' => 'float',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }
}
