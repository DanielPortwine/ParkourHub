<?php

namespace Database\Factories;

use App\Models\Challenge;
use App\Models\ChallengeEntry;
use App\Models\User;
use App\Scopes\VisibilityScope;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChallengeEntryFactory extends Factory
{
    protected $model = ChallengeEntry::class;

    public function definition()
    {
        return [
            'challenge_id' => Challenge::withoutGlobalScope(VisibilityScope::class)->inRandomOrder()->first()->id,
            'user_id' => User::inRandomOrder()->first()->id,
            'youtube' => 'Oykjn35X3EY',
        ];
    }
}
