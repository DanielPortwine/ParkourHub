<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Challenge extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'difficulty',
        'video',
        'youtube',
    ];

    public function entries()
    {
        return $this->hasMany('App\ChallengeEntry');
    }

    public function spot()
    {
        return $this->belongsTo('App\Spot');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function views()
    {
        return $this->hasMany('App\ChallengeView');
    }
}
