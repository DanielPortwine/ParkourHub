<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChallengeEntry extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'video',
        'youtube',
    ];

    public function challenge()
    {
        return $this->belongsTo('App\Challenge');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
