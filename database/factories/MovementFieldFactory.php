<?php

namespace Database\Factories;

use App\Models\MovementField;
use Illuminate\Database\Eloquent\Factories\Factory;

class MovementFieldFactory extends Factory
{
    protected $model = MovementField::class;

    public function definition()
    {
        $name = $this->faker->word;

        return [
            'name' => $name,
            'input_type' => $this->faker->randomElement(['number']),
            'label' => ucfirst($name),
            'unit' => $this->faker->word,
            'small_text' => $this->faker->sentence,
        ];
    }
}
