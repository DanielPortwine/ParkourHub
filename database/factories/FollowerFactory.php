<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Follower;
use App\User;
use Faker\Generator as Faker;

$factory->define(Follower::class, function (Faker $faker) {
    return [
        'user_id' => User::inRandomOrder()->first()->id,
        'follower_id' => User::inRandomOrder()->first()->id,
        'accepted' => true,
    ];
});
