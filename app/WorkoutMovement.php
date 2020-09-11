<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WorkoutMovement extends Model
{
    protected $fillable = [
        'reps',
        'weight',
        'duration',
        'distance',
        'height',
        'feeling',
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function workout()
    {
        return $this->belongsTo('App\Workouts');
    }

    public function movement()
    {
        return $this->belongsTo('App\Movement');
    }
}
