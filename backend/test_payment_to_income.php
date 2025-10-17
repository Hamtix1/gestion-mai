<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== PRUEBA DE SINCRONIZACIÓN PAGO → INGRESO ===\n\n";

// Obtener una esterilización con saldo pendiente
$sterilization = App\Models\Sterilization::where('payment_status', '!=', 'paid')
    ->where('balance', '>', 0)
    ->first();

if (!$sterilization) {
    echo "❌ No hay esterilizaciones con saldo pendiente\n";
    exit;
}

echo "📋 Esterilización seleccionada:\n";
echo "  ID: {$sterilization->id}\n";
echo "  Mascota: {$sterilization->pet_name}\n";
echo "  Propietario: {$sterilization->owner_full_name}\n";
echo "  Total: \${$sterilization->total_price}\n";
echo "  Pagado: \${$sterilization->total_paid}\n";
echo "  Saldo: \${$sterilization->balance}\n";
echo "  Estado pago: {$sterilization->payment_status}\n\n";

// Simular autenticación
auth()->loginUsingId(1);

echo "💳 Registrando pago por el saldo completo...\n";

// Crear un pago por el saldo restante
$payment = App\Models\Payment::create([
    'sterilization_id' => $sterilization->id,
    'amount' => $sterilization->balance,
    'payment_method' => 'cash',
    'reference_number' => 'TEST-' . time(),
    'notes' => 'Pago de prueba para sincronización automática',
    'received_by' => 1,
]);

echo "✓ Pago creado: ID {$payment->id}, Monto: \${$payment->amount}\n\n";

// Refrescar esterilización
$sterilization->refresh();

echo "📋 Estado actualizado de la esterilización:\n";
echo "  Pagado: \${$sterilization->total_paid}\n";
echo "  Saldo: \${$sterilization->balance}\n";
echo "  Estado pago: {$sterilization->payment_status}\n\n";

// Verificar si se creó el ingreso
$income = App\Models\Income::where('reference_number', 'STER-' . $sterilization->id)->first();

if ($income) {
    echo "✅ INGRESO CREADO AUTOMÁTICAMENTE:\n";
    echo "  ID: {$income->id}\n";
    echo "  Concepto: {$income->concept}\n";
    echo "  Descripción: {$income->description}\n";
    echo "  Monto: \${$income->amount}\n";
    echo "  Fuente: {$income->source}\n";
    echo "  Referencia: {$income->reference_number}\n";
    echo "  Fecha: {$income->income_date}\n";
    echo "\n🎉 ¡La sincronización funciona correctamente!\n";
} else {
    echo "❌ NO SE CREÓ EL INGRESO AUTOMÁTICAMENTE\n";
    echo "Verificar el código del modelo Payment::boot()\n";
}
