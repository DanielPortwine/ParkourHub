<?php

namespace Database\Seeders;

use App\Review;
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

                $spot->rating = round($spot->reviews->sum('rating') / count($spot->reviews));
                $spot->save();
            }
        }
    }
}
