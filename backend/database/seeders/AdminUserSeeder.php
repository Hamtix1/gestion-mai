<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder para crear el usuario administrador inicial
 */
class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener el rol de administrador
        $adminRole = Role::where('name', Role::ADMIN)->first();

        if (!$adminRole) {
            $this->command->error('El rol de administrador no existe. Ejecuta RoleSeeder primero.');
            return;
        }

        // Crear usuario administrador
        $admin = User::updateOrCreate(
            ['email' => 'admin@gestionmai.com'],
            [
                'name' => 'Administrador',
                'email' => 'admin@gestionmai.com',
                'password' => Hash::make('admin123'), // Cambiar en producción
                'role_id' => $adminRole->id,
                'is_active' => true,
                'phone' => null,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Usuario administrador creado exitosamente!');
        $this->command->info('Email: admin@gestionmai.com');
        $this->command->info('Password: admin123');
        $this->command->warn('¡IMPORTANTE! Cambia la contraseña en producción.');
    }
}
