<?php

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\User::class)->create([
            'name' => 'Admin',
            'email' => 'admin@parkourhub.com',
            'remember_token' => 'Hfkk1ZgTEAnGZHQdmyJQ8BNU761i94m0yzyHIgZFi6Ir0BLHotRoDFUjLGKA',
        ]);
        factory(App\User::class, 5)->create();
    }
}
