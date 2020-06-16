<?php

use App\Spot;
use App\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (User::pluck('id') as $user) {
            foreach (Spot::inRandomOrder()->limit(25)->get() as $spot) {
                factory(App\Review::class)->create(['spot_id' => $spot->id, 'user_id' => $user]);

                $spot->rating = round($spot->reviews->sum('rating') / count($spot->reviews));
                $spot->save();
            }
        }
    }
}
