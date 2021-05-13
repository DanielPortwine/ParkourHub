<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Follower extends Model
{
    use HasFactory;

    protected $table = 'user_followers';

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function follower()
    {
        return $this->belongsTo('App\Models\User');
    }
}
