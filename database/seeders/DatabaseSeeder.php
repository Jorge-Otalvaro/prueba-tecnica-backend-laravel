<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleAndPermissionSeeder::class);

        // Usuario admin con todos los permisos
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);
        $admin->assignRole('admin');

        // Usuario soporte con permisos de notas
        $support = User::factory()->create([
            'name' => 'Support Agent',
            'email' => 'support@example.com',
            'password' => 'password',
        ]);
        $support->assignRole('support');

        // Usuario viewer: puede ver notas pero NO agregar
        $viewer = User::factory()->create([
            'name' => 'Viewer User',
            'email' => 'viewer@example.com',
            'password' => 'password',
        ]);
        $viewer->assignRole('viewer');

        $this->call(PlayerSeeder::class);
    }
}
