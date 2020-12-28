<?php

namespace Database\Seeders;

use App\User;
use Illuminate\Database\Seeder;

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
        ]);
        User::factory()->times(5)->create();
    }
}
