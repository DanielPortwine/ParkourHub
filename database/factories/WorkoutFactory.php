<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Workout;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkoutFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Workout::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::inRandomOrder()->first()->id,
            'name' => $this->faker->word,
            'description' => $this->faker->realText(255),
            'visibility' => $this->faker->randomElement(['private', 'follower', 'public']),
            'thumbnail' => str_replace('public', '', $this->faker->image('public/storage/images/workouts', 640, 480, null, true)),
        ];
    }
}
