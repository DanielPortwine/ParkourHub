<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Hit;
use App\Spot;
use App\User;
use Faker\Generator as Faker;

$factory->define(Hit::class, function (Faker $faker) {
    return [
        'user_id' => User::inRandomOrder()->first()->id,
        'spot_id' => Spot::inRandomOrder()->first()->id,
    ];
});
