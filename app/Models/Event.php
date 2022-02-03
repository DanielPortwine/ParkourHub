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
use Illuminate\Support\Facades\Cache;
use Nicolaslopezj\Searchable\SearchableTrait;

class Event extends Model
{
    use SoftDeletes,
        Reportable,
        Commentable,
        SearchableTrait,
        HasFactory;

    protected $fillable = [
        'name',
        'description',
        'date_time',
        'video',
        'video_type',
        'youtube',
        'youtube_start',
        'thumbnail',
        'visibility',
        'link_access',
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
    }

    public function scopeEventBetween($query, $dates = [])
    {
        if (!empty($dates['from']) && !empty($dates['to'])) {
            $query->whereBetween('date_time', [$dates['from'], $dates['to']]);
        } else if (!empty($dates['from']) && empty($dates['to'])) {
            $query->where('date_time', '>=', $dates['from']);
        } else if (empty($dates['from']) && !empty($dates['to'])) {
            $query->where('date_time', '<=', $dates['to']);
        }
    }

    public function scopeFollowing($query, $following = false)
    {
        if ($following) {
            $followedUsers = Follower::where('follower_id', Auth::id())->pluck('user_id');

            $query->whereIn('user_id', $followedUsers);
        }
    }

    public function scopeHometown($query, $hometown = false)
    {
        if ($hometown && Auth::check() && count($boundaries = explode(',', Auth::user()->hometown_bounding)) === 4) {
            $query->whereHas('spots', function ($q) use ($boundaries) {
                return $q->whereBetween('latitude', [$boundaries[0], $boundaries[1]])
                    ->whereBetween('longitude', [$boundaries[2], $boundaries[3]]);
            });
        }
    }

    public function scopeAttending($query, $attending = false)
    {
        if ($attending) {
            $query->whereHas('attendees', function ($q) {
                return $q->where('user_id', Auth::id())
                    ->where('accepted', true);
            });
        }
    }

    public function scopeApplied($query, $applied = false)
    {
        if ($applied) {
            $query->whereHas('attendees', function ($q) {
                return $q->where('user_id', Auth::id())
                    ->where('accepted', false);
            });
        }
    }

    public function scopeHistoric($query, $historic = false)
    {
        if ($historic) {
            $query->where('date_time', '<', now());
        } else {
            $query->where('date_time', '>=', now());
        }
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function spots()
    {
        return $this->belongsToMany('App\Models\Spot', 'spots_events');
    }

    public function attendees()
    {
        return $this->belongsToMany('App\Models\User', 'events_attendees');
    }
}
