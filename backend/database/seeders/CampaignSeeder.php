<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Campaign;
use App\Models\User;
use Carbon\Carbon;

class CampaignSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@gestionmai.com')->first();

        if (!$admin) {
            $this->command->error('Usuario admin no encontrado. Ejecuta AdminUserSeeder primero.');
            return;
        }

        $campaigns = [
            [
                'name' => 'Campaña de Esterilización Centro',
                'description' => 'Campaña de esterilización masiva en la zona centro de la ciudad. Dirigida a perros y gatos de familias de bajos recursos.',
                'start_date' => Carbon::now()->subDays(30)->format('Y-m-d'),
                'end_date' => Carbon::now()->addDays(30)->format('Y-m-d'),
                'location' => 'Centro Comunitario - Zona Centro',
                'available_slots' => 100,
                'used_slots' => 45,
                'dog_price' => 150.00,
                'cat_price' => 100.00,
                'status' => 'active',
                'is_visible_to_public' => true,
                'created_by' => $admin->id,
            ],
            [
                'name' => 'Esterilización Zona Norte 2025',
                'description' => 'Jornada especial de esterilización para la zona norte. Incluye consulta veterinaria gratuita.',
                'start_date' => Carbon::now()->addDays(15)->format('Y-m-d'),
                'end_date' => Carbon::now()->addDays(45)->format('Y-m-d'),
                'location' => 'Clínica Veterinaria del Norte',
                'available_slots' => 80,
                'used_slots' => 0,
                'dog_price' => 200.00,
                'cat_price' => 150.00,
                'status' => 'planned',
                'is_visible_to_public' => true,
                'created_by' => $admin->id,
            ],
            [
                'name' => 'Campaña Rural Octubre',
                'description' => 'Campaña de esterilización en comunidades rurales. Servicio gratuito para familias de zonas alejadas.',
                'start_date' => Carbon::now()->subDays(15)->format('Y-m-d'),
                'end_date' => Carbon::now()->addDays(15)->format('Y-m-d'),
                'location' => 'Múltiples comunidades rurales',
                'available_slots' => 60,
                'used_slots' => 35,
                'dog_price' => 0.00,
                'cat_price' => 0.00,
                'status' => 'active',
                'is_visible_to_public' => true,
                'created_by' => $admin->id,
            ],
            [
                'name' => 'Jornada Especial Septiembre',
                'description' => 'Campaña completada exitosamente durante el mes de septiembre.',
                'start_date' => Carbon::now()->subDays(60)->format('Y-m-d'),
                'end_date' => Carbon::now()->subDays(30)->format('Y-m-d'),
                'location' => 'Centro Veterinario Municipal',
                'available_slots' => 50,
                'used_slots' => 50,
                'dog_price' => 180.00,
                'cat_price' => 120.00,
                'status' => 'completed',
                'is_visible_to_public' => false,
                'created_by' => $admin->id,
            ],
            [
                'name' => 'Campaña Zona Sur - Cancelada',
                'description' => 'Campaña cancelada por motivos de fuerza mayor.',
                'start_date' => Carbon::now()->subDays(20)->format('Y-m-d'),
                'end_date' => Carbon::now()->subDays(10)->format('Y-m-d'),
                'location' => 'Parque de la Zona Sur',
                'available_slots' => 40,
                'used_slots' => 5,
                'dog_price' => 150.00,
                'cat_price' => 100.00,
                'status' => 'cancelled',
                'is_visible_to_public' => false,
                'created_by' => $admin->id,
            ],
        ];

        foreach ($campaigns as $campaignData) {
            Campaign::create($campaignData);
        }

        $this->command->info('✓ Se crearon ' . count($campaigns) . ' campañas de ejemplo');
    }
}
