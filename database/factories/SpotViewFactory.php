<?php

namespace Database\Factories;

use App\Models\Spot;
use App\Models\SpotView;
use App\Models\User;
use App\Scopes\VisibilityScope;
use Illuminate\Database\Eloquent\Factories\Factory;

class SpotViewFactory extends Factory
{
    protected $model = SpotView::class;

    public function definition()
    {
        return [
            'spot_id' => Spot::withoutGlobalScope(VisibilityScope::class)->inRandomOrder()->first()->id,
            'user_id' => User::inRandomOrder()->first()->id,
        ];
    }
}
