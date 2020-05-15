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

    public function challenges()
    {
        return $this->hasMany('App\Challenge');
    }

    public function views()
    {
        return $this->hasMany('App\SpotView');
    }
}
