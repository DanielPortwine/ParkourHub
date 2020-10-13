<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\MovementField;
use Faker\Generator as Faker;

$factory->define(MovementField::class, function (Faker $faker) {
    $name = $faker->word;

    return [
        'name' => $name,
        'input_type' => $faker->randomElement(['number']),
        'label' => ucfirst($name),
        'unit' => $faker->word,
        'small_text' => $faker->sentence,
    ];
});
