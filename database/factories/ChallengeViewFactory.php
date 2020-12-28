<?php

namespace Database\Factories;

use App\Challenge;
use App\ChallengeView;
use App\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChallengeViewFactory extends Factory
{
    protected $model = ChallengeView::class;

    public function definition()
    {
        return [
            'user_id' => User::inRandomOrder()->first()->id,
            'challenge_id' => Challenge::inRandomOrder()->first()->id,
        ];
    }
}
