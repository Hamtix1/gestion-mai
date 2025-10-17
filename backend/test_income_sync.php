<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== ESTADO ACTUAL ===\n";
echo "Esterilizaciones: " . App\Models\Sterilization::count() . "\n";
echo "Pagos: " . App\Models\Payment::count() . "\n";
echo "Ingresos: " . App\Models\Income::count() . "\n\n";

echo "\n=== ESTERILIZACIONES PAGADAS ===\n";
$paid = App\Models\Sterilization::where('payment_status', 'completed')->get();
echo "Total pagadas: " . $paid->count() . "\n";

foreach ($paid as $s) {
    echo "  - ID: {$s->id} | Mascota: {$s->pet_name} | Total: \${$s->total_paid}\n";
    
    // Verificar si tiene ingreso asociado
    $income = App\Models\Income::where('reference_number', 'STER-' . $s->id)->first();
    if ($income) {
        echo "    ✓ Tiene ingreso: ID {$income->id}, Monto: \${$income->amount}\n";
    } else {
        echo "    ✗ NO tiene ingreso asociado\n";
    }
}

echo "\n=== INGRESOS EXISTENTES ===\n";
$incomes = App\Models\Income::all();
foreach ($incomes as $income) {
    echo "  - ID: {$income->id} | Concepto: {$income->concept} | Monto: \${$income->amount} | Fuente: {$income->source}\n";
}
