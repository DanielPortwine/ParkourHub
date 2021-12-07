<?php

namespace App\Traits;

use App\Models\Comment;
use App\Models\User;

trait Commentable
{
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable', null, null, 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
