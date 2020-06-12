<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Challenge;
use App\ChallengeView;
use App\User;
use Faker\Generator as Faker;

$factory->define(ChallengeView::class, function (Faker $faker) {
    return [
        'user_id' => User::inRandomOrder()->first()->id,
        'challenge_id' => Challenge::inRandomOrder()->first()->id,
    ];
});
