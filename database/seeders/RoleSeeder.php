<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = Role::create(['name' => 'admin']);
        $tester = Role::create(['name' => 'tester']);

        $deleteContent = Permission::create(['name' => 'delete content']);
        $accessPremium = Permission::create(['name' => 'access premium']);

        $admin->givePermissionTo($deleteContent);
        $admin->givePermissionTo($accessPremium);
        $tester->givePermissionTo($accessPremium);
    }
}
