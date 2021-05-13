<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Laravel\Cashier\Billable;
use Nicolaslopezj\Searchable\SearchableTrait;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable,
        SoftDeletes,
        Billable,
        SearchableTrait,
        HasFactory,
        HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'settings', 'image'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $searchable = [
        'columns' => [
            'name' => 10,
            'email' => 5,
        ],
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

    public function isPremium()
    {
        $isPremium = Cache::remember('premium_' . Auth::id(), 10, function() {
            return Auth::user()->subscribedToPlan(env('STRIPE_PLAN'), 'premium') || Auth::user()->hasPermissionTo('access premium');
        });

        return $isPremium;
    }


    public function spots()
    {
        return $this->hasMany('App\Models\Spot');
    }

    public function hits()
    {
        return $this->hasMany('App\Models\Hit');
    }

    public function reviews()
    {
        return $this->hasMany('App\Models\Review');
    }

    public function spotComments()
    {
        return $this->hasMany('App\Models\SpotComment');
    }

    public function challenges()
    {
        return $this->hasMany('App\Models\Challenge');
    }

    public function challengeEntries()
    {
        return $this->hasMany('App\Models\ChallengeEntry');
    }

    public function followers()
    {
        return $this->belongsToMany('App\Models\User', 'user_followers', 'user_id', 'follower_id');
    }

    public function following()
    {
        return $this->belongsToMany('App\Models\User', 'user_followers', 'follower_id', 'user_id');
    }

    public function reports()
    {
        return $this->hasMany('App\Models\Report');
    }

    public function movements()
    {
        return $this->hasMany('App\Models\Movement');
    }

    public function equipment()
    {
        return $this->hasMany('App\Models\Equipment');
    }

    public function workouts()
    {
        return $this->hasMany('App\Models\Workout');
    }

    public function bookmarks()
    {
        return $this->belongsToMany('App\Models\Workout', 'workout_bookmarks');
    }

    public function planWorkouts()
    {
        return $this->belongsToMany('App\Models\Workout', 'workout_plans')->withPivot('id', 'date', 'recorded_workout_id');
    }

    public function baselineMovementFields()
    {
        return $this->belongsToMany('App\Models\MovementField', 'users_movements_baseline');
    }

    public function settingsLog()
    {
        return $this->hasMany('App\Models\UserSettingsLog');
    }
}
