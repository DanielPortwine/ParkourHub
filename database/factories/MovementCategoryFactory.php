<?php

namespace Database\Factories;

use App\Models\MovementCategory;
use App\Models\MovementType;
use Illuminate\Database\Eloquent\Factories\Factory;

class MovementCategoryFactory extends Factory
{
    protected $model = MovementCategory::class;

    public function definition()
    {
        return [
            'type_id' => MovementType::inRandomOrder()->first()->id,
            'name' => $this->faker->word,
            'colour' => $this->faker->randomElement(['green', 'pink', 'blue', 'orange', 'yellow', 'cyan']),
            'description' => $this->faker->paragraph,
        ];
    }
}
