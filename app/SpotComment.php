<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SpotComment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'comment',
        'image',
        'youtube',
        'video',
    ];

    public function spot()
    {
        return $this->belongsTo('App\Spot');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function likes()
    {
        return $this->hasMany('App\SpotCommentLike');
    }
}
