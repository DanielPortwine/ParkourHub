<?php

namespace App\Models;

use App\Scopes\BannedUserScope;
use App\Scopes\VisibilityScope;
use App\Traits\Commentable;
use App\Traits\Reportable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Nicolaslopezj\Searchable\SearchableTrait;

class Workout extends Model
{
    use SoftDeletes,
        Reportable,
        Commentable,
        SearchableTrait,
        HasFactory;

    protected $fillable = [
        'name',
        'description',
        'visibility',
        'thumbnail',
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
        static::addGlobalScope(new BannedUserScope);
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

    public function scopeBookmarked($query, $bookmarked = false)
    {
        if ($bookmarked) {
            $query->whereHas('bookmarks', function($q) {
                $q->where('user_id', Auth::id());
            });
        }
    }

    public function scopeFollowing($query, $following = false)
    {
        if ($following) {
            $followedUsers = Follower::where('follower_id', Auth::id())->pluck('user_id');

            return $query->whereIn('user_id', $followedUsers);
        }

        return $query;
    }

    public function scopePersonal($query, $personal = false)
    {
        if ($personal) {
            $query->where('user_id', Auth::id());
        }
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function movements()
    {
        return $this->hasMany('App\Models\WorkoutMovement');
    }

    public function bookmarks()
    {
        return $this->belongsToMany('App\Models\User', 'workout_bookmarks');
    }

    public function spots()
    {
        return $this->belongsToMany('App\Models\Spot', 'spots_workouts');
    }

    public function planUsers()
    {
        return $this->belongsToMany('App\Models\User', 'workout_plans')->withPivot('id', 'date', 'recorded_workout_id');
    }
}
