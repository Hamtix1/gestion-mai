<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sterilization;
use App\Models\Payment;
use App\Models\User;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@gestionmai.com')->first();

        // Obtener esterilizaciones que deberían tener pagos
        $sterilizations = Sterilization::whereIn('id', [1, 2, 4, 5])->get();

        foreach ($sterilizations as $sterilization) {
            // Limpiar pagos existentes
            $sterilization->payments()->delete();

            switch ($sterilization->id) {
                case 1: // Luna - Completamente pagada
                    Payment::create([
                        'sterilization_id' => $sterilization->id,
                        'amount' => 150.00,
                        'payment_method' => 'cash',
                        'reference_number' => 'PAY-20251001-0001',
                        'notes' => 'Pago completo en efectivo',
                        'received_by' => $admin->id,
                        'created_at' => now()->subDays(10),
                    ]);
                    break;

                case 2: // Michi - Pago parcial
                    Payment::create([
                        'sterilization_id' => $sterilization->id,
                        'amount' => 50.00,
                        'payment_method' => 'transfer',
                        'reference_number' => 'PAY-20251005-0001',
                        'notes' => 'Primer abono por transferencia',
                        'received_by' => $admin->id,
                        'created_at' => now()->subDays(5),
                    ]);
                    break;

                case 4: // Pelusa - Completamente pagada
                    Payment::create([
                        'sterilization_id' => $sterilization->id,
                        'amount' => 100.00,
                        'payment_method' => 'card',
                        'reference_number' => 'PAY-20251002-0001',
                        'notes' => 'Pago con tarjeta',
                        'received_by' => $admin->id,
                        'created_at' => now()->subDays(8),
                    ]);
                    break;

                case 5: // Max - Completamente pagada en dos pagos
                    Payment::create([
                        'sterilization_id' => $sterilization->id,
                        'amount' => 100.00,
                        'payment_method' => 'cash',
                        'reference_number' => 'PAY-20251003-0001',
                        'notes' => 'Primer pago en efectivo',
                        'received_by' => $admin->id,
                        'created_at' => now()->subDays(7),
                    ]);
                    Payment::create([
                        'sterilization_id' => $sterilization->id,
                        'amount' => 50.00,
                        'payment_method' => 'cash',
                        'reference_number' => 'PAY-20251004-0001',
                        'notes' => 'Segundo pago completando el total',
                        'received_by' => $admin->id,
                        'created_at' => now()->subDays(6),
                    ]);
                    break;
            }

            // Actualizar el estado de pago de la esterilización
            $sterilization->updatePaymentStatus();
        }

        $this->command->info('✓ Pagos de prueba creados exitosamente');
    }
}
