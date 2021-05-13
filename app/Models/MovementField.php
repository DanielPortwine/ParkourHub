<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovementField extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'input_type',
        'label',
        'unit',
        'small_text',
    ];

    public function movements()
    {
        return $this->belongsToMany('App\Models\Movement', 'movements_fields');
    }

    public function workoutMovementFields()
    {
        return $this->hasMany('App\Models\WorkoutMovementField');
    }

    public function userMovementBaseline()
    {
        return $this->belongsToMany('App\Models\User', 'users_movements_baseline');
    }
}
