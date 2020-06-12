<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Spot;
use App\SpotComment;
use App\User;
use Faker\Generator as Faker;

$factory->define(SpotComment::class, function (Faker $faker) {
    $commentTypes = [
        'comment',
        'image',
        'imageComment',
    ];
    $commentType = $commentTypes[$faker->randomKey($commentTypes)];
    return [
        'spot_id' => Spot::inRandomOrder()->first()->id,
        'user_id' => User::inRandomOrder()->first()->id,
        'comment' => in_array($commentType, ['comment', 'imageComment']) ? $faker->realText(255) : null,
        'image' => in_array($commentType, ['image', 'imageComment']) ? '/storage/images/spots/' . $faker->image('public/storage/images/spots', 640, 480, null, false) : null,
    ];
});
