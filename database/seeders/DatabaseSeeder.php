<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear roles de Shield
        $roles = ['super_admin', 'admin', 'agent', 'client'];
        
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // Crear usuario administrador por defecto si no existe
        $admin = User::firstOrCreate(
            ['email' => 'admin@rams.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );

        // Asignar rol super_admin al usuario admin
        if (!$admin->hasRole('super_admin')) {
            $admin->assignRole('super_admin');
        }

        $this->call(RolePermissionSeeder::class);

        // Crear plantillas de correo
        $this->call(EmailTemplateSeeder::class);
    }
}
