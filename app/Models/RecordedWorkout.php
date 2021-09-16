<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecordedWorkout extends Model
{
    use HasFactory;

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
        return $this->belongsTo('App\Models\User');
    }

    public function workout()
    {
        return $this->belongsTo('App\Models\Workout');
    }

    public function movements()
    {
        return $this->hasMany('App\Models\WorkoutMovement');
    }
}
