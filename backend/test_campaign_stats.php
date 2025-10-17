<?php

/**
 * Script de prueba para verificar estadísticas de campaña
 * 
 * Este script verifica que las estadísticas de campaña se calculen correctamente,
 * especialmente el total_collected (total recaudado de las esterilizaciones)
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Campaign;
use App\Models\Sterilization;

echo "========================================\n";
echo "📊 TEST DE ESTADÍSTICAS DE CAMPAÑA\n";
echo "========================================\n\n";

// Obtener la primera campaña con esterilizaciones
$campaign = Campaign::with('sterilizations')->first();

if (!$campaign) {
    echo "❌ No se encontró ninguna campaña\n";
    exit(1);
}

echo "📍 Campaña: {$campaign->name}\n";
echo "📅 ID: {$campaign->id}\n";
echo "─────────────────────────────────────\n\n";

// Obtener estadísticas
echo "🔍 ESTADÍSTICAS CALCULADAS:\n\n";

$sterilizations = $campaign->sterilizations;
echo "1️⃣  Total de Esterilizaciones: " . $sterilizations->count() . "\n";

$paymentStats = $campaign->getPaymentStats();
echo "\n💰 ESTADÍSTICAS DE PAGOS:\n";
echo "   - Total Esperado: $" . number_format($paymentStats['total_expected'], 2) . "\n";
echo "   - Total Recaudado: $" . number_format($paymentStats['total_collected'], 2) . "\n";
echo "   - Pagos Completos: " . $paymentStats['completed_payments'] . "\n";
echo "   - Pagos Parciales: " . $paymentStats['partial_payments'] . "\n";
echo "   - Pagos Pendientes: " . $paymentStats['pending_payments'] . "\n";

echo "\n📊 BALANCE FINANCIERO:\n";
echo "   - Total Ingresos: $" . number_format($campaign->getTotalIncome(), 2) . "\n";
echo "   - Total Egresos: $" . number_format($campaign->getTotalExpense(), 2) . "\n";
echo "   - Balance: $" . number_format($campaign->getBalance(), 2) . "\n";

echo "\n📋 DETALLE DE ESTERILIZACIONES:\n";
echo "─────────────────────────────────────\n";
foreach ($sterilizations as $ster) {
    echo sprintf(
        "• ID %d - %s (%s) - Total: $%s - Pagado: $%s - Estado: %s\n",
        $ster->id,
        $ster->pet_name,
        $ster->pet_type === 'dog' ? '🐕' : '🐱',
        number_format($ster->total_price, 2),
        number_format($ster->total_paid, 2),
        $ster->payment_status
    );
}

echo "\n========================================\n";

// Verificar que total_collected sea mayor que 0 si hay esterilizaciones pagadas
$hasPayments = $sterilizations->where('total_paid', '>', 0)->count() > 0;
$totalCollected = $paymentStats['total_collected'];

if ($hasPayments && $totalCollected > 0) {
    echo "✅ TEST PASSED: total_collected = $" . number_format($totalCollected, 2) . "\n";
} elseif (!$hasPayments && $totalCollected == 0) {
    echo "⚠️  WARNING: No hay pagos registrados, total_collected = $0\n";
} else {
    echo "❌ TEST FAILED: Inconsistencia en total_collected\n";
}

echo "========================================\n";
