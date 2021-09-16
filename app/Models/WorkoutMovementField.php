<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkoutMovementField extends Model
{
    use HasFactory;

    protected $fillable = [
        'movement_field_id',
        'workout_movement_id',
        'recorded_workout_id',
        'value',
    ];

    public function workoutMovement()
    {
        return $this->belongsTo('App\Models\WorkoutMovement');
    }

    public function field()
    {
        return $this->belongsTo('App\Models\MovementField', 'movement_field_id', 'id');
    }
}
