<?php

namespace App\Models;

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
        return $this->hasMany('App\Models\Movement', 'type_id', 'id');
    }

    public function categories()
    {
        return $this->hasMany('App\Models\MovementCategory', 'type_id', 'id');
    }
}
