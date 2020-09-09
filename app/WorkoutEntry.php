<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WorkoutEntry extends Model
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

    public function movementEntries()
    {
        return $this->hasMany('App\WorkoutMovementEntry');
    }
}
