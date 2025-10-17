<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== VERIFICAR Y CORREGIR ESTADOS DE PAGO ===\n\n";

// Obtener todas las esterilizaciones
$sterilizations = App\Models\Sterilization::all();

echo "Total de esterilizaciones: {$sterilizations->count()}\n\n";

foreach ($sterilizations as $s) {
    echo "ID: {$s->id} | Mascota: {$s->pet_name}\n";
    echo "  Total: \${$s->total_price} | Pagado: \${$s->total_paid} | Saldo: \${$s->balance}\n";
    echo "  Estado actual: {$s->payment_status}\n";
    
    // Recalcular el estado correcto
    $totalPagos = $s->payments()->sum('amount');
    $balanceReal = max(0, $s->total_price - $totalPagos);
    
    if ($totalPagos >= $s->total_price) {
        $estadoCorrecto = 'completed';
    } elseif ($totalPagos > 0) {
        $estadoCorrecto = 'partial';
    } else {
        $estadoCorrecto = 'pending';
    }
    
    echo "  Total pagos en DB: \${$totalPagos} | Balance real: \${$balanceReal}\n";
    echo "  Estado correcto: {$estadoCorrecto}\n";
    
    if ($s->payment_status !== $estadoCorrecto || $s->total_paid != $totalPagos || $s->balance != $balanceReal) {
        echo "  ⚠️  DESACTUALIZADO - Corrigiendo...\n";
        $s->updatePaymentStatus();
        echo "  ✓ Corregido\n";
    } else {
        echo "  ✓ Correcto\n";
    }
    
    echo "\n";
}

echo "\n=== VERIFICAR INGRESOS ===\n";
$completed = App\Models\Sterilization::where('payment_status', 'completed')->get();
echo "Esterilizaciones completamente pagadas: {$completed->count()}\n\n";

// Simular autenticación
auth()->loginUsingId(1);

foreach ($completed as $s) {
    $income = App\Models\Income::where('reference_number', 'STER-' . $s->id)->first();
    
    if ($income) {
        echo "✓ ID {$s->id} ({$s->pet_name}) - Ya tiene ingreso: \${$income->amount}\n";
    } else {
        echo "✗ ID {$s->id} ({$s->pet_name}) - Creando ingreso...\n";
        
        // Crear el ingreso manualmente
        App\Models\Income::create([
            'campaign_id' => $s->campaign_id,
            'concept' => 'Esterilización #' . $s->id . ' - ' . $s->pet_name,
            'description' => 'Ingreso por esterilización de ' . $s->pet_name . ' (Propietario: ' . $s->owner_full_name . ')',
            'amount' => $s->total_paid,
            'source' => 'sterilization',
            'income_date' => $s->scheduled_date ?? now()->toDateString(),
            'reference_number' => 'STER-' . $s->id,
            'registered_by' => 1,
        ]);
        
        echo "  ✓ Ingreso creado\n";
    }
}

echo "\n=== RESUMEN FINAL ===\n";
echo "Total ingresos: " . App\Models\Income::count() . "\n";
echo "Ingresos por esterilización: " . App\Models\Income::where('source', 'sterilization')->count() . "\n";
