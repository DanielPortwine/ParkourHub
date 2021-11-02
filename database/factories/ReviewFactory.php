<?php

namespace Database\Factories;

use App\Models\Review;
use App\Models\Spot;
use App\Models\User;
use App\Scopes\VisibilityScope;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition()
    {
        return [
            'spot_id' => Spot::withoutGlobalScope(VisibilityScope::class)->inRandomOrder()->first()->id,
            'user_id' => User::inRandomOrder()->first()->id,
            'rating' => floor($this->faker->numberBetween(1, 5)),
            'title' => $this->faker->word,
            'review' => $this->faker->realText(255),
            'visibility' => $this->faker->randomElement(['private', 'follower', 'public']),
        ];
    }
}
