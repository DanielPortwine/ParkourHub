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
        $user = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@parkourhub.com',
            'remember_token' => 'Hfkk1ZgTEAnGZHQdmyJQ8BNU761i94m0yzyHIgZFi6Ir0BLHotRoDFUjLGKA',
        ]);
        $user->assignRole('admin');

        User::factory()->times(5)->create();
    }
}
