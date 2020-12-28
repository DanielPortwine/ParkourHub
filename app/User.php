<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Nicolaslopezj\Searchable\SearchableTrait;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable,
        SoftDeletes,
        Billable,
        SearchableTrait,
        HasFactory;

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

    public function spots()
    {
        return $this->hasMany('App\Spot');
    }

    public function hits()
    {
        return $this->hasMany('App\Hit');
    }

    public function reviews()
    {
        return $this->hasMany('App\Review');
    }

    public function spotComments()
    {
        return $this->hasMany('App\SpotComment');
    }

    public function challenges()
    {
        return $this->hasMany('App\Challenge');
    }

    public function challengeEntries()
    {
        return $this->hasMany('App\ChallengeEntry');
    }

    public function followers()
    {
        return $this->belongsToMany('App\User', 'user_followers', 'user_id', 'follower_id');
    }

    public function following()
    {
        return $this->belongsToMany('App\User', 'user_followers', 'follower_id', 'user_id');
    }

    public function reports()
    {
        return $this->hasMany('App\Report');
    }

    public function movements()
    {
        return $this->hasMany('App\Movement');
    }

    public function equipment()
    {
        return $this->hasMany('App\Equipment');
    }

    public function bookmarks()
    {
        return $this->belongsToMany('App\Workout', 'workout_bookmarks');
    }

    public function planWorkouts()
    {
        return $this->belongsToMany('App\Workout', 'workout_plans')->withPivot('id', 'date');
    }

    public function baselineMovementFields()
    {
        return $this->belongsToMany('App\MovementField', 'users_movements_baseline');
    }

    public function settingsLog()
    {
        return $this->hasMany('App\UserSettingsLog');
    }
}
