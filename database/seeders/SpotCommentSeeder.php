<?php

namespace Database\Seeders;

use App\Models\Spot;
use App\Models\SpotComment;
use App\Models\User;
use Illuminate\Database\Seeder;

class SpotCommentSeeder extends Seeder
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
                SpotComment::factory()->times(rand(0, 3))->create(['spot_id' => $spot, 'user_id' => $user]);
            }
        }
    }
}
