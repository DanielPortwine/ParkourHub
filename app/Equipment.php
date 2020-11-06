<?php

namespace App;

use App\Traits\Reportable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipment extends Model
{
    use SoftDeletes;
    use Reportable;

    protected $fillable = [
        'name',
        'description',
        'image',
        'required',
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function movements()
    {
        return $this->belongsToMany('App\Movement', 'movements_equipments', 'equipment_id', 'movement_id');
    }
}
