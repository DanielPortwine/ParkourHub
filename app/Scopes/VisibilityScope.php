<?php

namespace App\Scopes;

use App\Follower;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

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
        $builder->where('visibility', 'public')
            ->orWhere(function($q) use ($model) {
                $q->where('visibility', 'follower')
                    ->whereIn($model->getTable() . '.user_id', Follower::where('follower_id', Auth::id())->where('accepted', true)->pluck('user_id')->toArray());
            })
            ->orWhere($model->getTable() . '.user_id', Auth::id());
    }
}
