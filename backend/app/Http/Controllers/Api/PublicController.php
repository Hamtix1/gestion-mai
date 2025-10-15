<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Sterilization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controlador Público
 * 
 * Endpoints públicos para visitantes (sin autenticación)
 */
class PublicController extends Controller
{
    /**
     * Listar campañas activas y visibles al público
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function campaigns(Request $request): JsonResponse
    {
        $campaigns = Campaign::active()
            ->visibleToPublic()
            ->withAvailableSlots()
            ->select([
                'id',
                'name',
                'description',
                'start_date',
                'end_date',
                'location',
                'available_slots',
                'used_slots',
                'dog_price',
                'cat_price',
                'status'
            ])
            ->orderBy('start_date', 'asc')
            ->paginate($request->get('per_page', 10));

        // Agregar cupos restantes a cada campaña
        $campaigns->getCollection()->transform(function ($campaign) {
            return [
                ...$campaign->toArray(),
                'remaining_slots' => $campaign->getRemainingSlots(),
            ];
        });

        return response()->json($campaigns, 200);
    }

    /**
     * Obtener detalle de una campaña pública
     * 
     * @param Campaign $campaign
     * @return JsonResponse
     */
    public function campaignDetails(Campaign $campaign): JsonResponse
    {
        // Verificar que la campaña sea visible al público
        if (!$campaign->is_visible_to_public) {
            return response()->json([
                'message' => 'Campaña no disponible',
            ], 404);
        }

        $details = [
            'id' => $campaign->id,
            'name' => $campaign->name,
            'description' => $campaign->description,
            'start_date' => $campaign->start_date,
            'end_date' => $campaign->end_date,
            'location' => $campaign->location,
            'available_slots' => $campaign->available_slots,
            'used_slots' => $campaign->used_slots,
            'remaining_slots' => $campaign->getRemainingSlots(),
            'dog_price' => $campaign->dog_price,
            'cat_price' => $campaign->cat_price,
            'status' => $campaign->status,
            'has_available_slots' => $campaign->hasAvailableSlots(),
        ];

        return response()->json($details, 200);
    }

    /**
     * Consultar estado de esterilización por cédula
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function checkSterilizationStatus(Request $request): JsonResponse
    {
        $request->validate([
            'owner_id_number' => 'required|string',
        ]);

        $sterilizations = Sterilization::with(['campaign:id,name,location'])
            ->byOwner($request->owner_id_number)
            ->select([
                'id',
                'campaign_id',
                'owner_full_name',
                'pet_name',
                'pet_type',
                'total_price',
                'total_paid',
                'balance',
                'payment_status',
                'scheduled_date',
                'scheduled_time',
                'status',
                'created_at'
            ])
            ->latest()
            ->get();

        if ($sterilizations->isEmpty()) {
            return response()->json([
                'message' => 'No se encontraron esterilizaciones para esta cédula',
                'sterilizations' => [],
            ], 200);
        }

        return response()->json([
            'message' => 'Esterilizaciones encontradas',
            'sterilizations' => $sterilizations,
        ], 200);
    }

    /**
     * Obtener cupos disponibles por fecha (para agendamiento)
     * 
     * @param Campaign $campaign
     * @param Request $request
     * @return JsonResponse
     */
    public function availableSlotsByDate(Campaign $campaign, Request $request): JsonResponse
    {
        // Verificar que la campaña sea visible al público
        if (!$campaign->is_visible_to_public) {
            return response()->json([
                'message' => 'Campaña no disponible',
            ], 404);
        }

        $request->validate([
            'date' => 'required|date|after_or_equal:today',
        ]);

        $date = $request->date;

        // Verificar que la fecha esté dentro del rango de la campaña
        if ($date < $campaign->start_date || $date > $campaign->end_date) {
            return response()->json([
                'message' => 'La fecha está fuera del rango de la campaña',
                'campaign_start_date' => $campaign->start_date,
                'campaign_end_date' => $campaign->end_date,
            ], 422);
        }

        // Contar esterilizaciones agendadas para esa fecha
        $scheduledCount = Sterilization::where('campaign_id', $campaign->id)
            ->whereDate('scheduled_date', $date)
            ->whereIn('status', [
                Sterilization::STATUS_SCHEDULED,
                Sterilization::STATUS_IN_PROGRESS
            ])
            ->count();

        $dailyLimit = ceil($campaign->available_slots / 
            ($campaign->end_date->diffInDays($campaign->start_date) + 1));

        $availableForDate = max(0, $dailyLimit - $scheduledCount);

        return response()->json([
            'date' => $date,
            'available_slots' => $availableForDate,
            'scheduled_count' => $scheduledCount,
            'daily_limit' => $dailyLimit,
            'has_availability' => $availableForDate > 0,
        ], 200);
    }

    /**
     * Obtener estadísticas públicas
     * 
     * @return JsonResponse
     */
    public function publicStatistics(): JsonResponse
    {
        $stats = [
            'active_campaigns' => Campaign::active()->visibleToPublic()->count(),
            'total_sterilizations_completed' => Sterilization::where('status', Sterilization::STATUS_COMPLETED)->count(),
            'dogs_sterilized' => Sterilization::where('pet_type', Sterilization::PET_TYPE_DOG)
                ->where('status', Sterilization::STATUS_COMPLETED)
                ->count(),
            'cats_sterilized' => Sterilization::where('pet_type', Sterilization::PET_TYPE_CAT)
                ->where('status', Sterilization::STATUS_COMPLETED)
                ->count(),
        ];

        return response()->json($stats, 200);
    }

    /**
     * Formulario de contacto/solicitud de información
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function contactRequest(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'message' => 'required|string|max:1000',
            'campaign_id' => 'nullable|exists:campaigns,id',
        ]);

        // Aquí podrías guardar la solicitud en una tabla de contactos
        // o enviar un email al equipo administrativo
        
        // Por ahora, solo retornamos un mensaje de éxito
        return response()->json([
            'message' => 'Mensaje enviado exitosamente. Nos pondremos en contacto pronto.',
        ], 200);
    }
}
