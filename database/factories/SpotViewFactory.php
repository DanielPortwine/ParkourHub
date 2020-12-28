<?php

namespace Database\Factories;

use App\Spot;
use App\SpotView;
use App\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SpotViewFactory extends Factory
{
    protected $model = SpotView::class;

    public function definition()
    {
        return [
            'spot_id' => Spot::inRandomOrder()->first()->id,
            'user_id' => User::inRandomOrder()->first()->id,
        ];
    }
}
