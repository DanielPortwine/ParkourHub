<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Movement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'youtube',
        'youtube_start',
        'video',
        'video_type',
    ];

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

    public function scopeCategory($query, $category = null)
    {
        if (!empty($category)) {
            $query->where('category_id', $category);
        }
    }

    public function category()
    {
        return $this->belongsTo('App\MovementCategory');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function spots()
    {
        return $this->belongsToMany('App\Spot', 'spots_movements');
    }

    public function reports()
    {
        return $this->morphMany('App\Report', 'reportable');
    }
}
