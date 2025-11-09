<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupMetrics extends Model
{
    protected $table = 'group_metrics';
    protected $fillable = [
        'group_id',
        'discipline_id',
        'average_score',
        'engaging_score'
    ];
}
