<?php

namespace Database\Factories;

use App\Hit;
use App\Spot;
use App\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class HitFactory extends Factory
{
    protected $model = Hit::class;

    public function definition()
    {
        return [
            'user_id' => User::inRandomOrder()->first()->id,
            'spot_id' => Spot::inRandomOrder()->first()->id,
        ];
    }
}
