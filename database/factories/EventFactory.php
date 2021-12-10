<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Event::class;

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
            'date_time' => $this->faker->dateTimeBetween('-3 days', '+1 month'),
            'visibility' => $this->faker->randomElement(['private', 'follower', 'public']),
            'link_access' => $this->faker->boolean,
            'accept_method' => $this->faker->randomElement(['none', 'invite', 'accept']),
            'youtube' => 'Oykjn35X3EY',
            'thumbnail' => str_replace('public', '', $this->faker->image('public/storage/images/events', 640, 480, null, true)),
        ];
    }
}
