<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Challenge;
use App\Spot;
use App\User;
use Faker\Generator as Faker;

$factory->define(Challenge::class, function (Faker $faker) {
    return [
        'spot_id' => Spot::inRandomOrder()->first()->id,
        'user_id' => User::inRandomOrder()->first()->id,
        'name' => $faker->word,
        'description' => $faker->realText(255),
        'difficulty' => floor($faker->numberBetween(1, 5)),
        'youtube' => 'Oykjn35X3EY',
        'thumbnail' => '/storage/images/spots/' . $faker->image('public/storage/images/spots', 640, 480, null, false),
    ];
});
