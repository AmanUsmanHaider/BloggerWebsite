<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'blogger', 'guard_name' => 'web']);


        // Permissions
        Permission::create(['name' => 'create categories', 'guard_name' => 'web']);
        Permission::create(['name' => 'create tags', 'guard_name' => 'web']);
        Permission::create(['name' => 'create posts', 'guard_name' => 'web']);


        // Assign permissions to roles
        Role::findByName('admin')->givePermissionTo(['create categories', 'create tags',]);
        Role::findByName('blogger')->givePermissionTo(['create posts']);
    }
}
