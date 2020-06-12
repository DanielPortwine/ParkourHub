<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Spot;
use App\SpotView;
use App\User;
use Faker\Generator as Faker;

$factory->define(SpotView::class, function (Faker $faker) {
    return [
        'spot_id' => Spot::inRandomOrder()->first()->id,
        'user_id' => User::inRandomOrder()->first()->id,
    ];
});
