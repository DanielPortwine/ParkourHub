<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SpotView extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'spot_id',
        'user_id',
    ];

    public function spot()
    {
        return $this->belongsTo('App\Spot');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
