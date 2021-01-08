<?php

namespace Database\Seeders;

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run()
    {
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@parkourhub.com',
            'remember_token' => 'Hfkk1ZgTEAnGZHQdmyJQ8BNU761i94m0yzyHIgZFi6Ir0BLHotRoDFUjLGKA',
            'stripe_id' => 'cus_IiVXVY1uEiuIkj',
            'card_brand' => 'visa',
            'card_last_four' => '0005',
        ]);
        DB::table('subscription_items')->insert([
            'id' => 1,
            'subscription_id' => 1,
            'stripe_id' => 'si_IiVXBPYk7cnQUq',
            'stripe_plan' => 'price_1HjATwK6fbrAzEA3nQLx07VE',
            'quantity' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('subscriptions')->insert([
            'id' => 1,
            'user_id' => 1,
            'name' => 'premium',
            'stripe_id' => 'sub_IiVXtq16UwN0nD',
            'stripe_status' => 'active',
            'stripe_plan' => 'price_1HjATwK6fbrAzEA3nQLx07VE',
            'quantity' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        User::factory()->times(5)->create();
    }
}
