<?php

namespace App\Models;

use App\Scopes\VisibilityScope;
use App\Traits\Reportable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Nicolaslopezj\Searchable\SearchableTrait;

class Movement extends Model
{
    use SoftDeletes,
        Reportable,
        SearchableTrait,
        HasFactory;

    protected $fillable = [
        'name',
        'description',
        'youtube',
        'youtube_start',
        'video',
        'video_type',
        'visibility',
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

    public function scopeType($query, $type = null)
    {
        if (!empty($type)) {
            $query->where('type_id', $type);
        }
    }

    public function scopeCategory($query, $category = null)
    {
        if (!empty($category)) {
            $query->where('category_id', $category);
        }
    }

    public function scopeExercise($query, $exercise = null)
    {
        if (!empty($exercise)) {
            $query->whereHas('exercises', function($q) use($exercise) {
                $q->where('exercise_id', $exercise);
            });
        }
    }

    public function scopeEquipment($query, $equipment = null)
    {
        if (!empty($equipment)) {
            $query->whereHas('equipment', function($q) use($equipment) {
                $q->where('equipment_id', $equipment);
            });
        }
    }

    public function category()
    {
        return $this->belongsTo('App\Models\MovementCategory');
    }

    public function type()
    {
        return $this->belongsTo('App\Models\MovementType');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function spots()
    {
        return $this->belongsToMany('App\Models\Spot', 'spots_movements');
    }

    public function workouts()
    {
        return $this->hasMany('App\Models\WorkoutMovement');
    }

    public function progressions()
    {
        return $this->belongsToMany('App\Models\Movement', 'movements_progressions', 'advancement_id', 'progression_id');
    }

    public function advancements()
    {
        return $this->belongsToMany('App\Models\Movement', 'movements_progressions', 'progression_id', 'advancement_id');
    }

    public function exercises()
    {
        return $this->belongsToMany('App\Models\Movement', 'movements_exercises', 'move_id', 'exercise_id');
    }

    public function moves()
    {
        return $this->belongsToMany('App\Models\Movement', 'movements_exercises', 'exercise_id', 'move_id');
    }

    public function equipment()
    {
        return $this->belongsToMany('App\Models\Equipment', 'movements_equipments', 'movement_id', 'equipment_id');
    }

    public function fields()
    {
        return $this->belongsToMany('App\Models\MovementField', 'movements_fields');
    }
}
