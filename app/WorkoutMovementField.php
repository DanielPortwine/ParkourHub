<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WorkoutMovementField extends Model
{
    protected $fillable = [
        'movement_field_id',
        'workout_movement_id',
        'recorded_workout_id',
        'value',
    ];

    public function workoutMovement()
    {
        return $this->belongsTo('App\WorkoutMovement');
    }

    public function field()
    {
        return $this->belongsTo('App\MovementField', 'movement_field_id', 'id');
    }
}
