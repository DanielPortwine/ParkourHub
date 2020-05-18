<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Spot extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'private',
    ];

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
}
