<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controlador de Campañas
 * 
 * Gestiona las operaciones CRUD de campañas de esterilización
 */
class CampaignController extends Controller
{
    /**
     * Listar todas las campañas (con filtros opcionales)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = Campaign::with(['creator:id,name,email']);

        // Filtro por estado
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filtro por visibilidad pública
        if ($request->has('is_visible_to_public')) {
            $query->where('is_visible_to_public', $request->boolean('is_visible_to_public'));
        }

        // Filtro por rango de fechas
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->inDateRange($request->start_date, $request->end_date);
        }

        // Filtro para campañas con cupos disponibles
        if ($request->boolean('with_slots')) {
            $query->withAvailableSlots();
        }

        // Ordenar por fecha de inicio (más recientes primero)
        $query->orderBy('start_date', 'desc');

        $campaigns = $query->paginate($request->get('per_page', 15));

        return response()->json($campaigns, 200);
    }

    /**
     * Crear una nueva campaña
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'location' => 'required|string|max:255',
            'available_slots' => 'required|integer|min:1',
            'dog_price' => 'required|numeric|min:0',
            'cat_price' => 'required|numeric|min:0',
            'status' => 'sometimes|in:planned,active,completed,cancelled',
            'is_visible_to_public' => 'sometimes|boolean',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['used_slots'] = 0;

        $campaign = Campaign::create($validated);
        $campaign->load('creator:id,name,email');

        return response()->json([
            'message' => 'Campaña creada exitosamente',
            'campaign' => $campaign,
        ], 201);
    }

    /**
     * Mostrar una campaña específica
     * 
     * @param Campaign $campaign
     * @return JsonResponse
     */
    public function show(Campaign $campaign): JsonResponse
    {
        $campaign->load([
            'creator:id,name,email',
            'sterilizations' => function ($query) {
                $query->select('id', 'campaign_id', 'owner_full_name', 'owner_id_number', 'pet_name', 'pet_breed', 'pet_type', 'total_price', 'total_paid', 'payment_status', 'status', 'registered_by')
                      ->with('registeredBy:id,name')
                      ->latest();
            },
        ]);

        // Calcular estadísticas
        $paymentStats = $campaign->getPaymentStats();
        
        $stats = [
            'total_sterilizations' => $campaign->sterilizations->count(),
            'remaining_slots' => $campaign->getRemainingSlots(),
            'total_income' => $campaign->getTotalIncome(),
            'total_expense' => $campaign->getTotalExpense(),
            'balance' => $campaign->getBalance(),
            'total_collected' => $paymentStats['total_collected'],
            'total_expected' => $paymentStats['total_expected'],
            'completed_payments' => $paymentStats['completed_payments'],
            'partial_payments' => $paymentStats['partial_payments'],
            'pending_payments' => $paymentStats['pending_payments'],
        ];

        return response()->json([
            'campaign' => $campaign,
            'stats' => $stats,
        ], 200);
    }

    /**
     * Actualizar una campaña
     * 
     * @param Request $request
     * @param Campaign $campaign
     * @return JsonResponse
     */
    public function update(Request $request, Campaign $campaign): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after_or_equal:start_date',
            'location' => 'sometimes|required|string|max:255',
            'available_slots' => 'sometimes|required|integer|min:1',
            'dog_price' => 'sometimes|required|numeric|min:0',
            'cat_price' => 'sometimes|required|numeric|min:0',
            'status' => 'sometimes|in:planned,active,completed,cancelled',
            'is_visible_to_public' => 'sometimes|boolean',
        ]);

        $campaign->update($validated);
        $campaign->load('creator:id,name,email');

        return response()->json([
            'message' => 'Campaña actualizada exitosamente',
            'campaign' => $campaign,
        ], 200);
    }

    /**
     * Eliminar una campaña (soft delete)
     * 
     * @param Campaign $campaign
     * @return JsonResponse
     */
    public function destroy(Campaign $campaign): JsonResponse
    {
        // Verificar que no tenga esterilizaciones asociadas
        if ($campaign->sterilizations()->count() > 0) {
            return response()->json([
                'message' => 'No se puede eliminar una campaña que tiene esterilizaciones registradas',
            ], 422);
        }

        $campaign->delete();

        return response()->json([
            'message' => 'Campaña eliminada exitosamente',
        ], 200);
    }

    /**
     * Obtener estadísticas generales de campañas
     * 
     * @return JsonResponse
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_campaigns' => Campaign::count(),
            'active_campaigns' => Campaign::active()->count(),
            'planned_campaigns' => Campaign::where('status', Campaign::STATUS_PLANNED)->count(),
            'completed_campaigns' => Campaign::where('status', Campaign::STATUS_COMPLETED)->count(),
            'total_slots_available' => Campaign::active()->sum('available_slots'),
            'total_slots_used' => Campaign::active()->sum('used_slots'),
        ];

        return response()->json($stats, 200);
    }
}
