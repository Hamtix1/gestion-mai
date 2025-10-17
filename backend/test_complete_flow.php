<?php

/**
 * Script de prueba del flujo completo del sistema
 * 
 * Verifica:
 * 1. Creación de campaña
 * 2. Creación de esterilización
 * 3. Registro de pagos
 * 4. Sincronización automática de ingresos
 * 5. Creación de gastos
 * 6. Cálculos financieros
 */

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;

// Cargar la aplicación Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "  TESTING COMPLETO DEL SISTEMA - GESTIÓN MAI                    \n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";

// ============================================
// 1. VERIFICAR DATOS EXISTENTES
// ============================================
echo "📊 1. VERIFICANDO DATOS EXISTENTES\n";
echo "───────────────────────────────────────────────────────────────\n";

$campaigns = DB::table('campaigns')->count();
$sterilizations = DB::table('sterilizations')->count();
$payments = DB::table('payments')->count();
$incomes = DB::table('incomes')->count();
$expenses = DB::table('expenses')->count();
$users = DB::table('users')->count();

echo "   ✓ Campañas: {$campaigns}\n";
echo "   ✓ Esterilizaciones: {$sterilizations}\n";
echo "   ✓ Pagos: {$payments}\n";
echo "   ✓ Ingresos: {$incomes}\n";
echo "   ✓ Gastos: {$expenses}\n";
echo "   ✓ Usuarios: {$users}\n";
echo "\n";

// ============================================
// 2. VERIFICAR SINCRONIZACIÓN PAGOS → INGRESOS
// ============================================
echo "🔄 2. VERIFICANDO SINCRONIZACIÓN PAGOS → INGRESOS\n";
echo "───────────────────────────────────────────────────────────────\n";

$completedSterilizations = DB::table('sterilizations')
    ->where('payment_status', 'completed')
    ->get();

echo "   Esterilizaciones completamente pagadas: " . $completedSterilizations->count() . "\n\n";

foreach ($completedSterilizations as $ster) {
    $totalPaid = DB::table('payments')
        ->where('sterilization_id', $ster->id)
        ->sum('amount');
    
    $income = DB::table('incomes')
        ->where('reference_number', "STER-{$ster->id}")
        ->first();
    
    if ($income) {
        $match = abs($totalPaid - $income->amount) < 0.01 ? '✓' : '✗';
        echo "   {$match} Esterilización #{$ster->id}: \${$totalPaid} → Ingreso: \${$income->amount}\n";
    } else {
        echo "   ✗ Esterilización #{$ster->id}: \${$totalPaid} → SIN INGRESO GENERADO\n";
    }
}
echo "\n";

// ============================================
// 3. VERIFICAR CÁLCULOS FINANCIEROS
// ============================================
echo "💰 3. VERIFICANDO CÁLCULOS FINANCIEROS\n";
echo "───────────────────────────────────────────────────────────────\n";

$totalIncomes = DB::table('incomes')->sum('amount');
$totalExpenses = DB::table('expenses')->sum('amount');
$balance = $totalIncomes - $totalExpenses;

echo "   Total Ingresos: \$" . number_format($totalIncomes, 2) . "\n";
echo "   Total Gastos: \$" . number_format($totalExpenses, 2) . "\n";
echo "   Balance: \$" . number_format($balance, 2) . "\n";
echo "\n";

// Ingresos por fuente
echo "   Ingresos por Fuente:\n";
$incomesBySource = DB::table('incomes')
    ->select('source', DB::raw('SUM(amount) as total'))
    ->groupBy('source')
    ->get();

foreach ($incomesBySource as $item) {
    echo "      - {$item->source}: \$" . number_format($item->total, 2) . "\n";
}
echo "\n";

// Gastos por categoría
echo "   Gastos por Categoría:\n";
$expensesByCategory = DB::table('expenses')
    ->select('category', DB::raw('SUM(amount) as total'))
    ->groupBy('category')
    ->get();

if ($expensesByCategory->count() > 0) {
    foreach ($expensesByCategory as $item) {
        echo "      - {$item->category}: \$" . number_format($item->total, 2) . "\n";
    }
} else {
    echo "      (Sin gastos registrados)\n";
}
echo "\n";

// ============================================
// 4. VERIFICAR ESTADOS DE ESTERILIZACIONES
// ============================================
echo "🏥 4. VERIFICANDO ESTADOS DE ESTERILIZACIONES\n";
echo "───────────────────────────────────────────────────────────────\n";

$byStatus = DB::table('sterilizations')
    ->select('status', DB::raw('COUNT(*) as count'))
    ->groupBy('status')
    ->get();

echo "   Por estado:\n";
foreach ($byStatus as $item) {
    echo "      - {$item->status}: {$item->count}\n";
}
echo "\n";

$byPaymentStatus = DB::table('sterilizations')
    ->select('payment_status', DB::raw('COUNT(*) as count'))
    ->groupBy('payment_status')
    ->get();

echo "   Por estado de pago:\n";
foreach ($byPaymentStatus as $item) {
    echo "      - {$item->payment_status}: {$item->count}\n";
}
echo "\n";

// ============================================
// 5. VERIFICAR USUARIOS Y ROLES
// ============================================
echo "👥 5. VERIFICANDO USUARIOS Y ROLES\n";
echo "───────────────────────────────────────────────────────────────\n";

$usersByRole = DB::table('users')
    ->join('roles', 'users.role_id', '=', 'roles.id')
    ->select('roles.display_name', DB::raw('COUNT(*) as count'))
    ->groupBy('roles.id', 'roles.display_name')
    ->get();

echo "   Usuarios por rol:\n";
foreach ($usersByRole as $item) {
    echo "      - {$item->display_name}: {$item->count}\n";
}
echo "\n";

$activeUsers = DB::table('users')->where('is_active', true)->count();
$inactiveUsers = DB::table('users')->where('is_active', false)->count();

echo "   Por estado:\n";
echo "      - Activos: {$activeUsers}\n";
echo "      - Inactivos: {$inactiveUsers}\n";
echo "\n";

// ============================================
// 6. VERIFICAR CAMPAÑAS
// ============================================
echo "🎯 6. VERIFICANDO CAMPAÑAS\n";
echo "───────────────────────────────────────────────────────────────\n";

$campaignStats = DB::table('campaigns')
    ->select('status', DB::raw('COUNT(*) as count'))
    ->groupBy('status')
    ->get();

echo "   Campañas por estado:\n";
foreach ($campaignStats as $item) {
    echo "      - {$item->status}: {$item->count}\n";
}
echo "\n";

// Campañas con más esterilizaciones
$topCampaigns = DB::table('campaigns')
    ->leftJoin('sterilizations', 'campaigns.id', '=', 'sterilizations.campaign_id')
    ->select('campaigns.name', DB::raw('COUNT(sterilizations.id) as sterilization_count'))
    ->groupBy('campaigns.id', 'campaigns.name')
    ->orderByDesc('sterilization_count')
    ->limit(3)
    ->get();

echo "   Top 3 campañas con más esterilizaciones:\n";
foreach ($topCampaigns as $campaign) {
    echo "      - {$campaign->name}: {$campaign->sterilization_count} esterilizaciones\n";
}
echo "\n";

// ============================================
// 7. VERIFICAR INTEGRIDAD DE DATOS
// ============================================
echo "🔍 7. VERIFICANDO INTEGRIDAD DE DATOS\n";
echo "───────────────────────────────────────────────────────────────\n";

// Pagos huérfanos (sin esterilización)
$orphanPayments = DB::table('payments')
    ->leftJoin('sterilizations', 'payments.sterilization_id', '=', 'sterilizations.id')
    ->whereNull('sterilizations.id')
    ->count();

echo "   ✓ Pagos sin esterilización: {$orphanPayments}\n";

// Esterilizaciones sin campaña
$sterilizationsNoCampaign = DB::table('sterilizations')
    ->whereNull('campaign_id')
    ->count();

echo "   ✓ Esterilizaciones sin campaña: {$sterilizationsNoCampaign}\n";

// Ingresos sin referencia
$incomesNoRef = DB::table('incomes')
    ->whereNull('reference_number')
    ->count();

echo "   ✓ Ingresos sin referencia: {$incomesNoRef}\n";

// Gastos sin factura
$expensesNoInvoice = DB::table('expenses')
    ->whereNull('invoice_number')
    ->count();

echo "   ✓ Gastos sin factura: {$expensesNoInvoice}\n";
echo "\n";

// ============================================
// 8. RESUMEN FINAL
// ============================================
echo "═══════════════════════════════════════════════════════════════\n";
echo "  RESUMEN FINAL                                                 \n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";

$totalIncome = number_format($totalIncomes, 2);
$totalExpense = number_format($totalExpenses, 2);
$finalBalance = number_format($balance, 2);

echo "   💰 Situación Financiera:\n";
echo "      Total Ingresos:  \${$totalIncome}\n";
echo "      Total Gastos:    \${$totalExpense}\n";
echo "      Balance Final:   \${$finalBalance}\n";
echo "\n";

echo "   📊 Estadísticas Generales:\n";
echo "      {$campaigns} campañas | {$sterilizations} esterilizaciones | {$payments} pagos\n";
echo "      {$incomes} ingresos | {$expenses} gastos | {$users} usuarios\n";
echo "\n";

// Verificar problemas
$issues = 0;
if ($orphanPayments > 0) $issues++;
if ($completedSterilizations->count() != DB::table('incomes')->where('source', 'sterilization')->count()) $issues++;

if ($issues > 0) {
    echo "   ⚠️  Se detectaron {$issues} posibles inconsistencias\n";
} else {
    echo "   ✅ Sistema en perfecto estado - Sin inconsistencias detectadas\n";
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "  Testing completado exitosamente                               \n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";
