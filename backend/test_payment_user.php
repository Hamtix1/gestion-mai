<?php

/**
 * Script de prueba para verificar datos de pagos con usuario receptor
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Payment;

echo "========================================\n";
echo "🔍 VERIFICACIÓN DE DATOS DE PAGOS\n";
echo "========================================\n\n";

// Obtener pagos con relación de usuario receptor
$payments = Payment::with([
    'sterilization:id,owner_full_name,pet_name',
    'receivedBy:id,name,email'
])->get();

echo "📊 Total de pagos: " . $payments->count() . "\n\n";

foreach ($payments as $payment) {
    echo "─────────────────────────────────────\n";
    echo "ID Pago: {$payment->id}\n";
    echo "Monto: \${$payment->amount}\n";
    echo "Método: {$payment->payment_method}\n";
    echo "received_by (ID): {$payment->received_by}\n";
    
    if ($payment->receivedBy) {
        echo "✅ Recibido por: {$payment->receivedBy->name} ({$payment->receivedBy->email})\n";
    } else {
        echo "❌ Sin usuario receptor asignado\n";
    }
    
    if ($payment->sterilization) {
        echo "Esterilización: #{$payment->sterilization->id} - {$payment->sterilization->pet_name}\n";
    }
    echo "\n";
}

echo "========================================\n";
echo "🔍 ESTRUCTURA JSON\n";
echo "========================================\n\n";

if ($payments->count() > 0) {
    $firstPayment = $payments->first();
    echo "Ejemplo de JSON de pago:\n";
    echo json_encode($firstPayment->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    echo "\n\n";
}

echo "========================================\n";
