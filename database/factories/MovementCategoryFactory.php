<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\MovementCategory;
use App\MovementType;
use Faker\Generator as Faker;

$factory->define(MovementCategory::class, function (Faker $faker) {
    return [
        'type_id' => MovementType::inRandomOrder()->first()->id,
        'name' => $faker->word,
        'colour' => $faker->randomElement(['green', 'pink', 'blue', 'orange', 'yellow', 'cyan']),
        'description' => $faker->paragraph,
    ];
});
