<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sterilization;
use App\Models\Campaign;
use App\Models\User;
use Carbon\Carbon;

class SterilizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@gestionmai.com')->first();
        $campaigns = Campaign::whereIn('status', ['active', 'completed'])->get();

        if ($campaigns->isEmpty()) {
            $this->command->warn('No hay campañas activas o completadas. Ejecuta CampaignSeeder primero.');
            return;
        }

        $sterilizations = [
            // Campaña Centro (ID 1)
            [
                'campaign_id' => $campaigns->first()->id,
                'owner_full_name' => 'María González Pérez',
                'owner_id_number' => '12345678A',
                'owner_phone' => '555-0101',
                'owner_email' => 'maria.gonzalez@example.com',
                'owner_address' => 'Calle Principal #123, Zona Centro',
                'pet_name' => 'Luna',
                'pet_type' => 'dog',
                'pet_gender' => 'female',
                'pet_breed' => 'Labrador Mestizo',
                'pet_age_months' => 24,
                'pet_weight' => 15.5,
                'total_price' => 150.00,
                'total_paid' => 150.00,
                'balance' => 0.00,
                'payment_status' => 'completed',
                'scheduled_date' => Carbon::now()->subDays(10)->format('Y-m-d'),
                'scheduled_time' => '09:00:00',
                'status' => 'completed',
                'notes' => 'Cirugía exitosa. Recuperación normal.',
                'registered_by' => $admin->id,
            ],
            [
                'campaign_id' => $campaigns->first()->id,
                'owner_full_name' => 'Juan Martínez López',
                'owner_id_number' => '87654321B',
                'owner_phone' => '555-0102',
                'owner_email' => 'juan.martinez@example.com',
                'owner_address' => 'Avenida 2 #456, Zona Centro',
                'pet_name' => 'Michi',
                'pet_type' => 'cat',
                'pet_gender' => 'male',
                'pet_breed' => 'Siamés',
                'pet_age_months' => 18,
                'pet_weight' => 4.2,
                'total_price' => 100.00,
                'total_paid' => 50.00,
                'balance' => 50.00,
                'payment_status' => 'partial',
                'scheduled_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
                'scheduled_time' => '10:30:00',
                'status' => 'scheduled',
                'notes' => 'Primer pago realizado. Pendiente completar pago.',
                'registered_by' => $admin->id,
            ],
            [
                'campaign_id' => $campaigns->first()->id,
                'owner_full_name' => 'Ana Rodríguez Silva',
                'owner_id_number' => '11223344C',
                'owner_phone' => '555-0103',
                'owner_email' => 'ana.rodriguez@example.com',
                'owner_address' => 'Colonia Centro, Casa 789',
                'pet_name' => 'Rocky',
                'pet_type' => 'dog',
                'pet_gender' => 'male',
                'pet_breed' => 'Pastor Alemán',
                'pet_age_months' => 36,
                'pet_weight' => 28.0,
                'total_price' => 150.00,
                'total_paid' => 0.00,
                'balance' => 150.00,
                'payment_status' => 'pending',
                'scheduled_date' => Carbon::now()->addDays(3)->format('Y-m-d'),
                'scheduled_time' => '11:00:00',
                'status' => 'scheduled',
                'notes' => 'Pendiente de pago inicial.',
                'registered_by' => $admin->id,
            ],
            [
                'campaign_id' => $campaigns->first()->id,
                'owner_full_name' => 'Carlos Fernández Ruiz',
                'owner_id_number' => '55667788D',
                'owner_phone' => '555-0104',
                'owner_email' => null,
                'owner_address' => 'Barrio El Progreso',
                'pet_name' => 'Pelusa',
                'pet_type' => 'cat',
                'pet_gender' => 'female',
                'pet_breed' => 'Doméstico',
                'pet_age_months' => 12,
                'pet_weight' => 3.8,
                'total_price' => 100.00,
                'total_paid' => 100.00,
                'balance' => 0.00,
                'payment_status' => 'completed',
                'scheduled_date' => Carbon::now()->subDays(5)->format('Y-m-d'),
                'scheduled_time' => '14:00:00',
                'status' => 'completed',
                'notes' => 'Todo en orden.',
                'registered_by' => $admin->id,
            ],
            [
                'campaign_id' => $campaigns->first()->id,
                'owner_full_name' => 'Laura Sánchez Morales',
                'owner_id_number' => '99887766E',
                'owner_phone' => '555-0105',
                'owner_email' => 'laura.sanchez@example.com',
                'owner_address' => 'Zona Centro, Edificio Azul Apto 3B',
                'pet_name' => 'Max',
                'pet_type' => 'dog',
                'pet_gender' => 'male',
                'pet_breed' => 'Beagle',
                'pet_age_months' => 48,
                'pet_weight' => 12.5,
                'total_price' => 150.00,
                'total_paid' => 150.00,
                'balance' => 0.00,
                'payment_status' => 'completed',
                'scheduled_date' => Carbon::now()->subDays(15)->format('Y-m-d'),
                'scheduled_time' => '09:30:00',
                'status' => 'completed',
                'notes' => null,
                'registered_by' => $admin->id,
            ],
        ];

        foreach ($sterilizations as $sterilizationData) {
            Sterilization::create($sterilizationData);
        }

        // Actualizar used_slots de la campaña
        $firstCampaign = $campaigns->first();
        $firstCampaign->used_slots = Sterilization::where('campaign_id', $firstCampaign->id)->count();
        $firstCampaign->save();

        $this->command->info('✓ Se crearon ' . count($sterilizations) . ' esterilizaciones de ejemplo');
    }
}
