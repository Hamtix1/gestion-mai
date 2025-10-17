<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Ejecutar seeders en orden
        $this->call([
            RoleSeeder::class,
            AdminUserSeeder::class,
            CampaignSeeder::class,
            SterilizationSeeder::class,
            PaymentSeeder::class, // Crea pagos de prueba y sincroniza con ingresos
        ]);

        $this->command->info('¡Base de datos inicializada exitosamente!');
    }
}
