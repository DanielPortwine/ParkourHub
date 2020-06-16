<?php

use App\SpotComment;
use App\User;
use Illuminate\Database\Seeder;

class SpotCommentLikeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (User::pluck('id') as $user) {
            foreach (SpotComment::inRandomOrder()->limit(25)->pluck('id') as $comment) {
                factory(App\SpotCommentLike::class)->create(['spot_comment_id' => $comment, 'user_id' => $user]);
            }
        }
    }
}
