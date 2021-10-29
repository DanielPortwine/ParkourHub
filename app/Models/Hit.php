<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'spot_id',
        'completed_at',
    ];

    public function scopeTicked($query, $ticked = false)
    {
        if ($ticked) {
            return $query->whereNotNull('completed_at');
        }

        return $query;
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function spot()
    {
        return $this->belongsTo('App\Models\Spot');
    }
}
