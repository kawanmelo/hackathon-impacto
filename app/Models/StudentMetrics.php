<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentMetrics extends Model
{

    protected $table = 'student_metrics';

    protected $fillable = [
        'student_id',
        'discipline_id',
        'average_score',
        'engaging_score',
    ];

    protected $casts = [
        'average_score' => 'float',
        'engaging_score' => 'float',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
