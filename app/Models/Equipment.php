<?php

namespace App\Models;

use App\Scopes\BannedUserScope;
use App\Scopes\CopyrightScope;
use App\Scopes\VisibilityScope;
use App\Traits\Reportable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Nicolaslopezj\Searchable\SearchableTrait;

class Equipment extends Model
{
    use SoftDeletes,
        Reportable,
        SearchableTrait,
        HasFactory;

    protected $fillable = [
        'name',
        'description',
        'image',
        'visibility',
        'copyright_infringed_at',
    ];

    protected $searchable = [
        'columns' => [
            'name' => 10,
            'description' => 8,
        ],
    ];

    protected static function booted()
    {
        static::addGlobalScope(new VisibilityScope);
        static::addGlobalScope(new BannedUserScope);
        static::addGlobalScope(new CopyrightScope);
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

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function movements()
    {
        return $this->belongsToMany('App\Models\Movement', 'movements_equipments', 'equipment_id', 'movement_id');
    }
}
