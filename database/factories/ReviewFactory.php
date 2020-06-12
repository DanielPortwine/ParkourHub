<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Review;
use App\Spot;
use App\User;
use Faker\Generator as Faker;

$factory->define(Review::class, function (Faker $faker) {
    return [
        'spot_id' => Spot::inRandomOrder()->first()->id,
        'user_id' => User::inRandomOrder()->first()->id,
        'rating' => floor($faker->numberBetween(0, 5)),
        'title' => $faker->word,
        'review' => $faker->realText(255),
    ];
});
