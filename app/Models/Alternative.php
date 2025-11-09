<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alternative extends Model
{
    protected $table = 'alternatives';
    protected $fillable = [
        'question_id',
        'content',
        'is_correct',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
