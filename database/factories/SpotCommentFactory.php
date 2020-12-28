<?php

namespace Database\Factories;

use App\Spot;
use App\SpotComment;
use App\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SpotCommentFactory extends Factory
{
    protected $model = SpotComment::class;

    public function definition()
    {
        $commentTypes = [
            'comment',
            'image',
            'imageComment',
        ];
        $commentType = $commentTypes[$this->faker->randomKey($commentTypes)];
        return [
            'spot_id' => Spot::inRandomOrder()->first()->id,
            'user_id' => User::inRandomOrder()->first()->id,
            'comment' => in_array($commentType, ['comment', 'imageComment']) ? $this->faker->realText(255) : null,
            'visibility' => $this->faker->randomElement(['private', 'follower', 'public']),
            'image' => in_array($commentType, ['image', 'imageComment']) ? $this->faker->image('public/storage/images/spot_comments', 640, 480, null, true) : null,
        ];
    }
}
