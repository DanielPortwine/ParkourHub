<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpotView extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'spot_id',
        'user_id',
    ];

    public function spot()
    {
        return $this->belongsTo('App\Models\Spot');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
