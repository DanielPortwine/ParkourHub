<?php

namespace Database\Factories;

use App\Models\Spot;
use App\Models\Comment;
use App\Models\User;
use App\Scopes\VisibilityScope;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition()
    {
        $commentTypes = [
            'comment',
            'image',
            'imageComment',
        ];
        $commentType = $commentTypes[$this->faker->randomKey($commentTypes)];
        $spot = Spot::withoutGlobalScope(VisibilityScope::class)->inRandomOrder()->first();
        return [
            'user_id' => User::inRandomOrder()->first()->id,
            'commentable_type' => Spot::class,
            'commentable_id' => !empty($spot) ? $spot->id : 1,
            'comment' => in_array($commentType, ['comment', 'imageComment']) ? $this->faker->realText(255) : null,
            'visibility' => $this->faker->randomElement(['private', 'follower', 'public']),
            'image' => in_array($commentType, ['image', 'imageComment']) ? str_replace('public', '', $this->faker->image('public/storage/images/comments', 640, 480, null, true)) : null,
        ];
    }
}
