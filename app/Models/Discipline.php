<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Discipline extends Model
{
    protected $table = 'disciplines';
    protected $fillable = [
        'name',
        'code',
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class, 'discipline_id');
    }
}
