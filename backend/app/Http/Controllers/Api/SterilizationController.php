<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Sterilization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Controlador de Esterilizaciones
 * 
 * Gestiona el registro y seguimiento de esterilizaciones
 */
class SterilizationController extends Controller
{
    /**
     * Listar esterilizaciones con filtros
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = Sterilization::with(['campaign:id,name,location', 'registeredBy:id,name']);

        // Filtro por campaña
        if ($request->has('campaign_id')) {
            $query->byCampaign($request->campaign_id);
        }

        // Filtro por estado de pago
        if ($request->has('payment_status')) {
            $query->byPaymentStatus($request->payment_status);
        }

        // Filtro por estado de esterilización
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filtro por cédula del propietario
        if ($request->has('owner_id_number')) {
            $query->byOwner($request->owner_id_number);
        }

        // Filtro por nombre del propietario
        if ($request->has('owner_name')) {
            $query->where('owner_full_name', 'like', '%' . $request->owner_name . '%');
        }

        // Filtro por tipo de mascota
        if ($request->has('pet_type')) {
            $query->where('pet_type', $request->pet_type);
        }

        // Filtro por fecha agendada
        if ($request->has('scheduled_date')) {
            $query->whereDate('scheduled_date', $request->scheduled_date);
        }

        // Ordenar por fecha de creación (más recientes primero)
        $query->latest();

        $sterilizations = $query->paginate($request->get('per_page', 15));

        return response()->json($sterilizations, 200);
    }

    /**
     * Registrar una nueva esterilización
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'campaign_id' => 'required|exists:campaigns,id',
            'owner_full_name' => 'required|string|max:255',
            'owner_id_number' => 'required|string|max:50',
            'owner_phone' => 'nullable|string|max:20',
            'owner_email' => 'nullable|email|max:255',
            'owner_address' => 'nullable|string',
            'pet_name' => 'required|string|max:255',
            'pet_type' => 'required|in:dog,cat',
            'pet_gender' => 'required|in:male,female',
            'pet_breed' => 'nullable|string|max:255',
            'pet_age_months' => 'nullable|integer|min:0',
            'pet_weight' => 'nullable|numeric|min:0',
            'scheduled_date' => 'nullable|date',
            'scheduled_time' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            // Verificar que la campaña tenga cupos disponibles
            $campaign = Campaign::findOrFail($validated['campaign_id']);
            
            if (!$campaign->hasAvailableSlots()) {
                return response()->json([
                    'message' => 'La campaña no tiene cupos disponibles',
                ], 422);
            }

            // Determinar el precio según el tipo de mascota
            $total_price = $validated['pet_type'] === 'dog' 
                ? $campaign->dog_price 
                : $campaign->cat_price;

            // Crear la esterilización
            $sterilization = Sterilization::create([
                ...$validated,
                'total_price' => $total_price,
                'total_paid' => 0,
                'balance' => $total_price,
                'payment_status' => Sterilization::PAYMENT_STATUS_PENDING,
                'status' => Sterilization::STATUS_SCHEDULED,
                'registered_by' => Auth::id(),
            ]);

            // Incrementar los cupos utilizados de la campaña
            $campaign->increment('used_slots');

            $sterilization->load(['campaign:id,name,location', 'registeredBy:id,name']);

            return response()->json([
                'message' => 'Esterilización registrada exitosamente',
                'sterilization' => $sterilization,
            ], 201);
        });
    }

    /**
     * Mostrar una esterilización específica
     * 
     * @param Sterilization $sterilization
     * @return JsonResponse
     */
    public function show(Sterilization $sterilization): JsonResponse
    {
        $sterilization->load([
            'campaign:id,name,location,start_date,end_date',
            'registeredBy:id,name,email',
            'payments' => function ($query) {
                $query->with('receivedBy:id,name')->latest();
            },
        ]);

        return response()->json($sterilization, 200);
    }

    /**
     * Actualizar una esterilización
     * 
     * @param Request $request
     * @param Sterilization $sterilization
     * @return JsonResponse
     */
    public function update(Request $request, Sterilization $sterilization): JsonResponse
    {
        $validated = $request->validate([
            'owner_full_name' => 'sometimes|required|string|max:255',
            'owner_id_number' => 'sometimes|required|string|max:50',
            'owner_phone' => 'nullable|string|max:20',
            'owner_email' => 'nullable|email|max:255',
            'owner_address' => 'nullable|string',
            'pet_name' => 'sometimes|required|string|max:255',
            'pet_gender' => 'sometimes|required|in:male,female',
            'pet_breed' => 'nullable|string|max:255',
            'pet_age_months' => 'nullable|integer|min:0',
            'pet_weight' => 'nullable|numeric|min:0',
            'scheduled_date' => 'nullable|date',
            'scheduled_time' => 'nullable|date_format:H:i',
            'status' => 'sometimes|in:scheduled,in_progress,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        $sterilization->update($validated);
        $sterilization->load(['campaign:id,name,location', 'registeredBy:id,name']);

        return response()->json([
            'message' => 'Esterilización actualizada exitosamente',
            'sterilization' => $sterilization,
        ], 200);
    }

    /**
     * Eliminar una esterilización (soft delete)
     * 
     * @param Sterilization $sterilization
     * @return JsonResponse
     */
    public function destroy(Sterilization $sterilization): JsonResponse
    {
        return DB::transaction(function () use ($sterilization) {
            // Verificar que no tenga pagos
            if ($sterilization->payments()->count() > 0) {
                return response()->json([
                    'message' => 'No se puede eliminar una esterilización que tiene pagos registrados',
                ], 422);
            }

            // Decrementar los cupos utilizados de la campaña
            $sterilization->campaign->decrement('used_slots');

            $sterilization->delete();

            return response()->json([
                'message' => 'Esterilización eliminada exitosamente',
            ], 200);
        });
    }

    /**
     * Obtener estadísticas de esterilizaciones
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function statistics(Request $request): JsonResponse
    {
        $query = Sterilization::query();

        // Filtro opcional por campaña
        if ($request->has('campaign_id')) {
            $query->byCampaign($request->campaign_id);
        }

        $stats = [
            'total' => $query->count(),
            'completed' => (clone $query)->where('status', Sterilization::STATUS_COMPLETED)->count(),
            'scheduled' => (clone $query)->where('status', Sterilization::STATUS_SCHEDULED)->count(),
            'in_progress' => (clone $query)->where('status', Sterilization::STATUS_IN_PROGRESS)->count(),
            'cancelled' => (clone $query)->where('status', Sterilization::STATUS_CANCELLED)->count(),
            'dogs' => (clone $query)->where('pet_type', Sterilization::PET_TYPE_DOG)->count(),
            'cats' => (clone $query)->where('pet_type', Sterilization::PET_TYPE_CAT)->count(),
            'payment_completed' => (clone $query)->where('payment_status', Sterilization::PAYMENT_STATUS_COMPLETED)->count(),
            'payment_pending' => (clone $query)->pendingPayment()->count(),
            'total_price' => (clone $query)->sum('total_price'),
            'total_paid' => (clone $query)->sum('total_paid'),
            'total_balance' => (clone $query)->sum('balance'),
        ];

        return response()->json($stats, 200);
    }
}
