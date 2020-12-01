<?php

namespace App;

use App\Traits\Reportable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipment extends Model
{
    use SoftDeletes;
    use Reportable;

    protected $fillable = [
        'name',
        'description',
        'image',
        'required',
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

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function movements()
    {
        return $this->belongsToMany('App\Movement', 'movements_equipments', 'equipment_id', 'movement_id');
    }
}
