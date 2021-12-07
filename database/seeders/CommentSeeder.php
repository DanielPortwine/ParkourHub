<?php

namespace Database\Seeders;

use App\Models\Spot;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
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
                Comment::factory()->times(rand(0, 3))->create(['commentable_type' => Spot::class, 'commentable_id' => $spot, 'user_id' => $user]);
            }
        }
    }
}
