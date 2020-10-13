<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MovementType extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];

    public function movements()
    {
        return $this->hasMany('App\Movement');
    }
}
