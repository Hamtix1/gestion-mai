<?php

/**
 * Script de prueba para verificar datos de vendedor en esterilizaciones
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Sterilization;
use App\Models\Campaign;

echo "========================================\n";
echo "🔍 VERIFICACIÓN DE VENDEDORES\n";
echo "========================================\n\n";

// Obtener esterilizaciones con relación de vendedor
$sterilizations = Sterilization::with('registeredBy:id,name,email')->get();

echo "📊 Total de esterilizaciones: " . $sterilizations->count() . "\n\n";

foreach ($sterilizations as $ster) {
    echo "─────────────────────────────────────\n";
    echo "ID: {$ster->id}\n";
    echo "Mascota: {$ster->pet_name}\n";
    echo "Propietario: {$ster->owner_full_name}\n";
    echo "registered_by (ID): {$ster->registered_by}\n";
    
    if ($ster->registeredBy) {
        echo "✅ Vendedor: {$ster->registeredBy->name} ({$ster->registeredBy->email})\n";
    } else {
        echo "❌ Sin vendedor asignado\n";
    }
    echo "\n";
}

echo "========================================\n";
echo "🔍 VERIFICACIÓN DE CAMPAÑA\n";
echo "========================================\n\n";

$campaign = Campaign::with([
    'sterilizations' => function ($query) {
        $query->select('id', 'campaign_id', 'owner_full_name', 'owner_id_number', 'pet_name', 'pet_breed', 'pet_type', 'total_price', 'total_paid', 'payment_status', 'status', 'registered_by')
              ->with('registeredBy:id,name')
              ->latest();
    }
])->first();

if ($campaign) {
    echo "Campaña: {$campaign->name}\n";
    echo "Esterilizaciones: {$campaign->sterilizations->count()}\n\n";
    
    foreach ($campaign->sterilizations as $ster) {
        echo "  - #{$ster->id} {$ster->pet_name}";
        echo " | registered_by: {$ster->registered_by}";
        
        if ($ster->registeredBy) {
            echo " | Vendedor: {$ster->registeredBy->name}\n";
        } else {
            echo " | ❌ Sin vendedor\n";
        }
    }
}

echo "\n========================================\n";
echo "🔍 ESTRUCTURA DE DATOS JSON\n";
echo "========================================\n\n";

if ($campaign && $campaign->sterilizations->count() > 0) {
    $firstSter = $campaign->sterilizations->first();
    echo "Ejemplo de JSON de esterilización:\n";
    echo json_encode($firstSter->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    echo "\n";
}

echo "\n========================================\n";
