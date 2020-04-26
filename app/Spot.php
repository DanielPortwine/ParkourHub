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

    public function challenges()
    {
        return $this->hasMany('App\Challenge');
    }
}
