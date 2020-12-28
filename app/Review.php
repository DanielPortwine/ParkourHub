<?php

namespace App;

use App\Traits\Reportable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    use SoftDeletes,
        Reportable,
        HasFactory;

    protected $fillable = [
        'spot_id',
        'user_id',
        'rating',
        'title',
        'review',
    ];

    public function scopeRating($query, $rating = null)
    {
        if (!empty($rating)) {
            return $query->where('rating', $rating);
        }

        return $query;
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

    public function scopeWithText($query)
    {
        return $query->whereNotNull('title')->orWhereNotNull('review');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function spot()
    {
        return $this->belongsTo('App\Spot');
    }

    public function reports()
    {
        return $this->morphMany('App\Report', 'reportable');
    }
}
