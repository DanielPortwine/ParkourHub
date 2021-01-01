<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovementType extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name',
    ];

    public function movements()
    {
        return $this->hasMany('App\Movement');
    }

    public function categories()
    {
        return $this->hasMany('App\MovementCategory', 'type_id', 'id');
    }
}
