<?php

namespace Database\Factories;

use App\Models\Spot;
use App\Models\SpotLocal;
use App\Models\User;
use App\Scopes\VisibilityScope;
use Illuminate\Database\Eloquent\Factories\Factory;

class SpotLocalFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SpotLocal::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'spot_id' => Spot::withoutGlobalScope(VisibilityScope::class)->inRandomOrder()->first()->id,
            'user_id' => User::inRandomOrder()->first()->id,
        ];
    }
}
