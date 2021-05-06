<?php

namespace App\Scopes;

use App\Follower;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class VisibilityScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $tableName = $model->getTable();
        $followers = Cache::remember('visibility_followers_' . $tableName . '_' . Auth::id(), 30, function() {
            return Follower::where('follower_id', Auth::id())->where('accepted', true)->pluck('user_id')->toArray();
        });
        $builder->where('visibility', 'public')
            ->orWhere(function($q) use ($tableName, $followers) {
                $q->where('visibility', 'follower')
                    ->whereIn($tableName . '.user_id', $followers);
            })
            ->orWhere($tableName . '.user_id', Auth::id());
    }
}
