<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RecordedWorkout extends Model
{
    public function scopeDateBetween($query, $dates = [])
    {
        if (!empty($dates['from']) && !empty($dates['to'])) {
            $query->whereBetween('created_at', [$dates['from'], $dates['to']]);
        } else if (!empty($dates['from']) && empty($dates['to'])) {
            $query->where('created_at', '>=', $dates['from']);
        } else if (empty($dates['from']) && !empty($dates['to'])) {
            $query->where('created_at', '<=', $dates['to']);
        }

        return $query;
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function workout()
    {
        return $this->belongsTo('App\Workout');
    }

    public function movements()
    {
        return $this->hasMany('App\WorkoutMovement');
    }
}
