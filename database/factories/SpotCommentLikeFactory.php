<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\SpotComment;
use App\SpotCommentLike;
use App\User;
use Faker\Generator as Faker;

$factory->define(SpotCommentLike::class, function (Faker $faker) {
    return [
        'spot_comment_id' => SpotComment::inRandomOrder()->first()->id,
        'user_id' => User::inRandomOrder()->first()->id,
    ];
});
