<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Challenge;
use App\ChallengeEntry;
use App\User;
use Faker\Generator as Faker;

$factory->define(ChallengeEntry::class, function (Faker $faker) {
    return [
        'challenge_id' => Challenge::inRandomOrder()->first()->id,
        'user_id' => User::inRandomOrder()->first()->id,
        'youtube' => 'Oykjn35X3EY',
    ];
});
