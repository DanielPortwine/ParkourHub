<?php

namespace App;

use App\Traits\Reportable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Spot extends Model
{
    use SoftDeletes;
    use Reportable;

    protected $fillable = [
        'name',
        'description',
        'private',
    ];

    public function scopeRating($query, $rating = null)
    {
        if (!empty($rating)) {
            return $query->where('rating', $rating);
        }

        return $query;
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

    public function scopeHitlist($query, $on = false)
    {
        if ($on) {
            $hits = Hit::whereHas('spot')
                ->where('user_id', Auth::id())
                ->pluck('spot_id');

            return $query->whereIn('id', $hits);
        }

        return $query;
    }

    public function scopeTicked($query, $ticked = false)
    {
        if ($ticked) {
            $hits = Hit::whereHas('spot')
                ->where('user_id', Auth::id())
                ->ticked($ticked)
                ->pluck('spot_id');

            return $query->whereIn('id', $hits);
        }

        return $query;
    }

    public function scopeFollowing($query, $following = false)
    {
        if ($following) {
            $followedUsers = Follower::where('follower_id', Auth::id())->pluck('user_id');

            return $query->whereIn('user_id', $followedUsers);
        }

        return $query;
    }

    public function scopeMovement($query, $movement = false)
    {
        if ($movement) {
            $query->whereHas('movements', function($q) use ($movement) {
                return $q->where('movements.id', $movement);
            });
        }
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function hits()
    {
        return $this->hasMany('App\Hit');
    }

    public function reviews()
    {
        return $this->hasMany('App\Review');
    }

    public function comments()
    {
        return $this->hasMany('App\SpotComment');
    }

    public function challenges()
    {
        return $this->hasMany('App\Challenge');
    }

    public function views()
    {
        return $this->hasMany('App\SpotView');
    }

    public function movements()
    {
        return $this->belongsToMany('App\Movement', 'spots_movements');
    }

    public function workouts()
    {
        return $this->belongsToMany('App\Workout', 'spots_workouts');
    }
}
