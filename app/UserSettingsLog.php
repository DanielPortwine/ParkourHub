<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserSettingsLog extends Model
{
    protected $fillable = [
        'user_id',
        'settings',
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
