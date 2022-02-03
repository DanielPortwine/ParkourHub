<?php

namespace App\Models;

use App\Scopes\BannedUserScope;
use App\Traits\Reportable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChallengeEntry extends Model
{
    use SoftDeletes,
        Reportable,
        HasFactory;

    protected $fillable = [
        'video',
        'youtube',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new BannedUserScope);
    }

    public function scopeDateBetween($query, $dates = [])
    {
        if (!empty($dates['from']) && !empty($dates['to'])) {
            $query->whereBetween('created_at', [$dates['from'], $dates['to']]);
        } else if (!empty($dates['from']) && empty($dates['to'])) {
            $query->where('created_at', '>=', $dates['from']);
        } else if (empty($dates['from']) && !empty($dates['to'])) {
            $query->where('created_at', '<=', $dates['to']);
        }

        return $query;
    }

    public function scopeWinner($query, $winner = null)
    {
        if (!empty($winner)) {
            return $query->where('winner', 1);
        }

        return $query;
    }

    public function challenge()
    {
        return $this->belongsTo('App\Models\Challenge');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
