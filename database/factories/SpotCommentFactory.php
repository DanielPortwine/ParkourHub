<?php

namespace Database\Factories;

use App\Models\Spot;
use App\Models\SpotComment;
use App\Models\User;
use App\Scopes\VisibilityScope;
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
            'spot_id' => Spot::withoutGlobalScope(VisibilityScope::class)->inRandomOrder()->first()->id,
            'user_id' => User::inRandomOrder()->first()->id,
            'comment' => in_array($commentType, ['comment', 'imageComment']) ? $this->faker->realText(255) : null,
            'visibility' => $this->faker->randomElement(['private', 'follower', 'public']),
            'image' => in_array($commentType, ['image', 'imageComment']) ? str_replace('public', '', $this->faker->image('public/storage/images/spot_comments', 640, 480, null, true)) : null,
        ];
    }
}
