<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MovementCategory extends Model
{
    public function type()
    {
        return $this->belongsTo('App\MovementType');
    }

    public function movements()
    {
        return $this->hasMany('App\Movement');
    }
}
