<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class Student extends Authenticatable
{
    use  Notifiable, HasRoles, HasApiTokens;

    protected $table = 'students';
    protected $fillable = [
        'turma_id',
        'nome',
        'email'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function metrics(): HasMany
    {
        return $this->hasMany(StudentMetrics::class);
    }


}
