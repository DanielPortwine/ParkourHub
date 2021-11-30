<?php

namespace App\Models;

use App\Scopes\VisibilityScope;
use App\Traits\Reportable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Nicolaslopezj\Searchable\SearchableTrait;

class Challenge extends Model
{
    use SoftDeletes,
        Reportable,
        SearchableTrait,
        HasFactory;

    protected $fillable = [
        'name',
        'description',
        'difficulty',
        'video',
        'youtube',
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

    public function scopeDifficulty($query, $difficulty = null)
    {
        if (!empty($difficulty)) {
            return $query->where('difficulty', $difficulty);
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

    public function scopeEntered($query, $entered = false)
    {
        if ($entered) {
            $entries = ChallengeEntry::whereHas('challenge')
                ->where('user_id', Auth::id())
                ->pluck('challenge_id');

            return $query->whereIn('id', $entries);
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

    public function scopeHometown($query, $hometown = false)
    {
        $boundaries = explode(',', Auth::user()->hometown_bounding);
        if ($hometown && count($boundaries) === 4) {
            $query->whereHas('spot', function ($q) use ($boundaries) {
                return $q->whereBetween('latitude', [$boundaries[0], $boundaries[1]])
                    ->whereBetween('longitude', [$boundaries[2], $boundaries[3]]);
            });
        }
    }

    public function entries()
    {
        return $this->hasMany('App\Models\ChallengeEntry');
    }

    public function spot()
    {
        return $this->belongsTo('App\Models\Spot');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function views()
    {
        return $this->hasMany('App\Models\ChallengeView');
    }
}
