<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkoutMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'movement_id',
        'workout_id',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function workout()
    {
        return $this->belongsTo('App\Models\Workout');
    }

    public function recordedWorkout()
    {
        return $this->belongsTo('App\Models\RecordedWorkout');
    }

    public function movement()
    {
        return $this->belongsTo('App\Models\Movement');
    }

    public function fields()
    {
        return $this->hasMany('App\Models\WorkoutMovementField');
    }
}
