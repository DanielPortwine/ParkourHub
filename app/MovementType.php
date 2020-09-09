<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MovementType extends Model
{
    public function movements()
    {
        return $this->hasMany('App\Movement');
    }
}
