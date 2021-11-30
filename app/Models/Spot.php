<?php

namespace App\Models;

use App\Scopes\VisibilityScope;
use App\Traits\Reportable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Nicolaslopezj\Searchable\SearchableTrait;

class Spot extends Model
{
    use SoftDeletes,
        Reportable,
        SearchableTrait,
        HasFactory;

    protected $fillable = [
        'name',
        'description',
        'rating',
        'visibility',
        'coordinates',
        'latitude',
        'longitude',
        'image',
    ];

    protected $searchable = [
        'columns' => [
            'name' => 10,
            'description' => 5,
        ],
    ];

    protected static function booted()
    {
        static::addGlobalScope(new VisibilityScope);
    }

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

    public function scopeHometown($query, $hometown = false)
    {
        $boundaries = explode(',', Auth::user()->hometown_bounding);
        if ($hometown && count($boundaries) === 4) {
            $query->whereBetween('latitude', [$boundaries[0], $boundaries[1]])
                ->whereBetween('longitude', [$boundaries[2], $boundaries[3]]);
        }
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function hits()
    {
        return $this->hasMany('App\Models\Hit');
    }

    public function reviews()
    {
        return $this->hasMany('App\Models\Review');
    }

    public function comments()
    {
        return $this->hasMany('App\Models\SpotComment');
    }

    public function challenges()
    {
        return $this->hasMany('App\Models\Challenge');
    }

    public function views()
    {
        return $this->hasMany('App\Models\SpotView');
    }

    public function movements()
    {
        return $this->belongsToMany('App\Models\Movement', 'spots_movements');
    }

    public function workouts()
    {
        return $this->belongsToMany('App\Models\Workout', 'spots_workouts');
    }
}
