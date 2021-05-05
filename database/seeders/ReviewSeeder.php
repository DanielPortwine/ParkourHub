<?php

namespace Database\Seeders;

use App\Review;
use App\Scopes\VisibilityScope;
use App\Spot;
use App\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run()
    {
        foreach (User::pluck('id') as $user) {
            foreach (Spot::inRandomOrder()->limit(25)->get() as $spot) {
                Review::factory()->create(['spot_id' => $spot->id, 'user_id' => $user]);

                $spot->rating = round($spot->reviews()->withoutGlobalScope(VisibilityScope::class)->get()->sum('rating') / count($spot->reviews()->withoutGlobalScope(VisibilityScope::class)->get()));
                $spot->save();
            }
        }
    }
}
