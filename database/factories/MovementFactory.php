<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Movement;
use App\MovementCategory;
use App\MovementType;
use App\User;
use Faker\Generator as Faker;

$factory->define(Movement::class, function (Faker $faker) {
    return [
        'category_id' => MovementCategory::inRandomOrder()->first()->id,
        'user_id' => User::inRandomOrder()->first()->id,
        'type_id' => MovementType::inRandomOrder()->first()->id,
        'name' => $faker->name,
        'description' => $faker->paragraph,
        'youtube' => $faker->randomElement(['fpX9dOcBjaQ', 'RHnXg6piz20', 'jPhuefvauuk', '_Ciuaz6duvw', 'KFfLUdnsvjY', 'Au2Zz7W99bQ', 'Mg7WANy8QE4', 'XUzkBa0p-SM']),
        'official' => $faker->boolean,
    ];
});
