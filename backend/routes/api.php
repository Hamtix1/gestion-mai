<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CampaignController;
use App\Http\Controllers\Api\FinancialController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PublicController;
use App\Http\Controllers\Api\SterilizationController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Aquí se registran las rutas de la API para la aplicación.
| Estas rutas son cargadas por el RouteServiceProvider y están
| asignadas al grupo de middleware "api".
|
*/

// ===== RUTAS PÚBLICAS (sin autenticación) =====
Route::prefix('public')->group(function () {
    // Campañas públicas
    Route::get('/campaigns', [PublicController::class, 'campaigns']);
    Route::get('/campaigns/{campaign}', [PublicController::class, 'campaignDetails']);
    
    // Consulta de estado de esterilización
    Route::post('/check-sterilization', [PublicController::class, 'checkSterilizationStatus']);
    
    // Cupos disponibles por fecha
    Route::get('/campaigns/{campaign}/available-slots', [PublicController::class, 'availableSlotsByDate']);
    
    // Estadísticas públicas
    Route::get('/statistics', [PublicController::class, 'publicStatistics']);
    
    // Formulario de contacto
    Route::post('/contact', [PublicController::class, 'contactRequest']);
});

// ===== RUTAS DE AUTENTICACIÓN =====
Route::prefix('auth')->group(function () {
    // Login (no requiere autenticación)
    Route::post('/login', [AuthController::class, 'login']);
    
    // Rutas protegidas con autenticación
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
    });
});

// ===== RUTAS PROTEGIDAS (requieren autenticación) =====
Route::middleware('auth:sanctum')->group(function () {
    
    // ===== RUTAS SOLO PARA ADMIN =====
    Route::middleware('admin')->group(function () {
        // Gestión de usuarios
        Route::apiResource('users', UserController::class);
        Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus']);
        
        // Registro de nuevos usuarios
        Route::post('auth/register', [AuthController::class, 'register']);
        
        // Reportes financieros completos
        Route::get('financial/report-by-campaign', [FinancialController::class, 'reportByCampaign']);
    });
    
    // ===== RUTAS PARA ADMIN Y VENDEDOR =====
    Route::middleware('seller')->group(function () {
        // Gestión de campañas
        Route::apiResource('campaigns', CampaignController::class);
        Route::get('campaigns-statistics', [CampaignController::class, 'statistics']);
        
        // Gestión de esterilizaciones
        Route::apiResource('sterilizations', SterilizationController::class);
        Route::get('sterilizations-statistics', [SterilizationController::class, 'statistics']);
        
        // Gestión de pagos
        Route::apiResource('payments', PaymentController::class);
        Route::get('payments/sterilization/{sterilization}', [PaymentController::class, 'bySterilization']);
        Route::get('payments-statistics', [PaymentController::class, 'statistics']);
        
        // Gestión financiera (ingresos y egresos)
        Route::prefix('financial')->group(function () {
            // Ingresos
            Route::get('incomes', [FinancialController::class, 'incomes']);
            Route::post('incomes', [FinancialController::class, 'storeIncome']);
            Route::put('incomes/{income}', [FinancialController::class, 'updateIncome']);
            Route::delete('incomes/{income}', [FinancialController::class, 'destroyIncome']);
            
            // Egresos
            Route::get('expenses', [FinancialController::class, 'expenses']);
            Route::post('expenses', [FinancialController::class, 'storeExpense']);
            Route::put('expenses/{expense}', [FinancialController::class, 'updateExpense']);
            Route::delete('expenses/{expense}', [FinancialController::class, 'destroyExpense']);
            
            // Resumen financiero
            Route::get('summary', [FinancialController::class, 'summary']);
        });
    });
});

// Ruta de prueba
Route::get('/ping', function () {
    return response()->json([
        'message' => 'API de Gestión MAI funcionando correctamente',
        'timestamp' => now()->toDateTimeString(),
    ]);
});
