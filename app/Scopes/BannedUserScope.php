<?php

namespace App\Scopes;

use App\Models\Follower;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class BannedUserScope implements Scope
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
        $fieldName = $tableName . '.user_id';
        if ($tableName === 'users') {
            $fieldName = 'users.id';
        }
        $bannedUsers = User::withoutGlobalScope(BannedUserScope::class)->whereNotNull('banned_at')->pluck('id')->toArray();
        $builder->whereNotIn($fieldName, $bannedUsers);
    }
}
