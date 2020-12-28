<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hit extends Model
{
    use HasFactory;

    protected $fillable = [
        'completed',
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
        return $this->belongsTo('App\User');
    }

    public function spot()
    {
        return $this->belongsTo('App\Spot');
    }
}
