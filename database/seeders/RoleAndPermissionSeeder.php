<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        Permission::create(['name' => 'view player notes']);
        Permission::create(['name' => 'add player notes']);

        // Create roles and assign permissions
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        $support = Role::create(['name' => 'support']);
        $support->givePermissionTo([
            'view player notes',
            'add player notes',
        ]);

        // Viewer: solo puede ver notas, no agregar
        $viewer = Role::create(['name' => 'viewer']);
        $viewer->givePermissionTo(['view player notes']);
    }
}
