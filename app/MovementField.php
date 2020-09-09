<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MovementField extends Model
{
    public function movements()
    {
        return $this->belongsToMany('App\Movements', 'movements_fields');
    }
}
