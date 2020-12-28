<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovementCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'colour',
        'description',
    ];

    public function type()
    {
        return $this->belongsTo('App\MovementType');
    }

    public function movements()
    {
        return $this->hasMany('App\Movement');
    }
}
