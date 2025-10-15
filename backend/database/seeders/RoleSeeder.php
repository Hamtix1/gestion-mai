<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Seeder para crear los roles del sistema
 */
class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => Role::ADMIN,
                'display_name' => 'Administrador',
                'description' => 'Usuario con acceso completo al sistema',
            ],
            [
                'name' => Role::SELLER,
                'display_name' => 'Vendedor',
                'description' => 'Usuario que puede registrar esterilizaciones y pagos',
            ],
            [
                'name' => Role::USER,
                'display_name' => 'Usuario',
                'description' => 'Usuario público/visitante con acceso limitado',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['name' => $role['name']],
                $role
            );
        }

        $this->command->info('Roles creados exitosamente!');
    }
}
