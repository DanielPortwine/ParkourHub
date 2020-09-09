<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WorkoutMovementEntry extends Model
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

    public function workoutEntry()
    {
        return $this->belongsTo('App\WorkoutEntries');
    }

    public function movement()
    {
        return $this->belongsTo('App\Movement');
    }
}
