<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Spot;
use App\User;
use Faker\Generator as Faker;
use proj4php\Point;
use proj4php\Proj;
use proj4php\Proj4php;

$factory->define(Spot::class, function (Faker $faker) {
    $proj4 = new Proj4php();
    $proj4326 = new Proj('EPSG:4326', $proj4);
    $proj3857 = new Proj('EPSG:3857', $proj4);

    $lat = $faker->latitude;
    $lon = $faker->longitude;
    $lonlat = new Point($lon, $lat, $proj4326);
    $coordinates = $proj4->transform($proj3857, $lonlat);

    return [
        'user_id' => User::inRandomOrder()->first()->id,
        'name' => $faker->word,
        'description' => $faker->realText(255),
        'visibility' => $faker->randomElement(['private', 'follower', 'public']),
        'coordinates' => $coordinates->x . ',' . $coordinates->y,
        'latitude' => $lat,
        'longitude' => $lon,
        'image' => '/storage/images/spots/' . $faker->image('public/storage/images/spots', 640, 480, null, false),
    ];
});
