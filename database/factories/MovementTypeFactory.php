<?php

namespace Database\Factories;

use App\MovementType;
use Illuminate\Database\Eloquent\Factories\Factory;

class MovementTypeFactory extends Factory
{
    protected $model = MovementType::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word,
        ];
    }
}
