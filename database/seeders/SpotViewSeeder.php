<?php

namespace Database\Seeders;

use App\Spot;
use App\SpotView;
use App\User;
use Illuminate\Database\Seeder;

class SpotViewSeeder extends Seeder
{
    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run()
    {
        foreach (User::pluck('id') as $user) {
            foreach (Spot::inRandomOrder()->limit(25)->pluck('id') as $spot) {
                SpotView::factory()->create(['spot_id' => $spot, 'user_id' => $user]);
            }
        }
    }
}
