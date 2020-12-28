<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChallengeView extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'challenge_id',
        'user_id',
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
