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

        $removeContent = Permission::create(['name' => 'remove content']);
        $accessPremium = Permission::create(['name' => 'access premium']);
        $manageReports = Permission::create(['name' => 'manage reports']);
        $officialise = Permission::create(['name' => 'officialise']);

        $admin->givePermissionTo($removeContent);
        $admin->givePermissionTo($accessPremium);
        $admin->givePermissionTo($manageReports);
        $admin->givePermissionTo($officialise);

        $tester->givePermissionTo($accessPremium);
    }
}
