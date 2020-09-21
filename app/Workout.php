<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Workout extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

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

    public function movements()
    {
        return $this->hasMany('App\WorkoutMovement');
    }

    public function bookmarks()
    {
        return $this->belongsToMany('App\User', 'workout_bookmarks');
    }

    public function spots()
    {
        return $this->belongsToMany('App\Spot', 'spots_workouts');
    }

    public function planUsers()
    {
        return $this->belongsToMany('App\User', 'workout_plans')->withPivot('id', 'date');
    }
}
