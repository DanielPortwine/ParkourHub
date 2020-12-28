<?php

namespace Database\Factories;

use App\Spot;
use App\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use proj4php\Point;
use proj4php\Proj;
use proj4php\Proj4php;

class SpotFactory extends Factory
{
    protected $model = Spot::class;

    public function definition()
    {
        $proj4 = new Proj4php();
        $proj4326 = new Proj('EPSG:4326', $proj4);
        $proj3857 = new Proj('EPSG:3857', $proj4);

        $lat = $this->faker->latitude;
        $lon = $this->faker->longitude;
        $lonlat = new Point($lon, $lat, $proj4326);
        $coordinates = $proj4->transform($proj3857, $lonlat);

        return [
            'user_id' => User::inRandomOrder()->first()->id,
            'name' => $this->faker->word,
            'description' => $this->faker->realText(255),
            'visibility' => $this->faker->randomElement(['private', 'follower', 'public']),
            'coordinates' => $coordinates->x . ',' . $coordinates->y,
            'latitude' => $lat,
            'longitude' => $lon,
            'image' => $this->faker->image('public/storage/images/spots', 640, 480, null, true),
        ];
    }
}
