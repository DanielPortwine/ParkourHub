<?php

namespace Database\Factories;

use App\Models\Movement;
use App\Models\MovementCategory;
use App\Models\MovementType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MovementFactory extends Factory
{
    protected $model = Movement::class;

    public function definition()
    {
        return [
            'category_id' => MovementCategory::inRandomOrder()->first()->id,
            'user_id' => User::inRandomOrder()->first()->id,
            'creator_id' => User::inRandomOrder()->first()->id,
            'type_id' => MovementType::inRandomOrder()->first()->id,
            'name' => $this->faker->name,
            'description' => $this->faker->paragraph,
            'visibility' => $this->faker->randomElement(['private', 'follower', 'public']),
            'youtube' => $this->faker->randomElement(['fpX9dOcBjaQ', 'RHnXg6piz20', 'jPhuefvauuk', '_Ciuaz6duvw', 'KFfLUdnsvjY', 'Au2Zz7W99bQ', 'Mg7WANy8QE4', 'XUzkBa0p-SM']),
            'official' => $this->faker->boolean,
        ];
    }
}
