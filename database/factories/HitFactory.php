<?php

namespace Database\Factories;

use App\Models\Hit;
use App\Models\Spot;
use App\Models\User;
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
