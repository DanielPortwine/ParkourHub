<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WorkoutMovement extends Model
{
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function workout()
    {
        return $this->belongsTo('App\Workout');
    }

    public function recordedWorkout()
    {
        return $this->belongsTo('App\RecordedWorkout');
    }

    public function movement()
    {
        return $this->belongsTo('App\Movement');
    }

    public function fields()
    {
        return $this->hasMany('App\WorkoutMovementField');
    }
}
