<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SpotCommentLike extends Model
{
    public $timestamps = false;

    public function spotComment()
    {
        return $this->belongsTo('App\SpotComment');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
