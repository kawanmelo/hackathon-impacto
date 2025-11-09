<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    protected $table = 'groups';
    protected $fillable = [
        'grade',
        'shift'
    ];

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'group_id');
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'teacher_group', 'group_id', 'teacher_id');
    }

    public function metrics(): HasMany
    {
        return $this->hasMany(GroupMetrics::class);
    }
}
