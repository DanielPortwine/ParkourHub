<?php

namespace Database\Factories;

use App\Challenge;
use App\Spot;
use App\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChallengeFactory extends Factory
{
    protected $model = Challenge::class;

    public function definition()
    {
        return [
            'spot_id' => Spot::inRandomOrder()->first()->id,
            'user_id' => User::inRandomOrder()->first()->id,
            'name' => $this->faker->word,
            'description' => $this->faker->realText(255),
            'difficulty' => floor($this->faker->numberBetween(1, 5)),
            'visibility' => $this->faker->randomElement(['private', 'follower', 'public']),
            'youtube' => 'Oykjn35X3EY',
            'thumbnail' => str_replace('public', '', $this->faker->image('public/storage/images/challenges', 640, 480, null, true)),
        ];
    }
}
