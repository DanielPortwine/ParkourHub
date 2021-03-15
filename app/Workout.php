<?php

namespace App;

use App\Scopes\VisibilityScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Nicolaslopezj\Searchable\SearchableTrait;

class Workout extends Model
{
    use SoftDeletes,
        SearchableTrait;

    protected $fillable = [
        'name',
        'description',
        'visibility',
    ];

    protected $searchable = [
        'columns' => [
            'name' => 10,
            'description' => 8,
        ],
    ];

    protected static function booted()
    {
        static::addGlobalScope(new VisibilityScope);
    }

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
        return $this->belongsToMany('App\User', 'workout_plans')->withPivot('id', 'date', 'recorded_workout_id');
    }
}
