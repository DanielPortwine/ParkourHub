<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipment extends Model
{
    use SoftDeletes;

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

    public function reports()
    {
        return $this->morphMany('App\Report', 'reportable');
    }

    public function movements()
    {
        return $this->belongsToMany('App\Movement', 'movements_equipments', 'equipment_id', 'movement_id');
    }
}
