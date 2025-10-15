<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Sterilization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Controlador de Pagos
 * 
 * Gestiona los pagos y abonos de las esterilizaciones
 */
class PaymentController extends Controller
{
    /**
     * Listar pagos con filtros
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = Payment::with([
            'sterilization:id,campaign_id,owner_full_name,pet_name,total_price,total_paid,balance',
            'sterilization.campaign:id,name',
            'receivedBy:id,name'
        ]);

        // Filtro por esterilización
        if ($request->has('sterilization_id')) {
            $query->where('sterilization_id', $request->sterilization_id);
        }

        // Filtro por método de pago
        if ($request->has('payment_method')) {
            $query->byMethod($request->payment_method);
        }

        // Filtro por rango de fechas
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->inDateRange($request->start_date, $request->end_date);
        }

        // Filtro por usuario que recibió el pago
        if ($request->has('received_by')) {
            $query->where('received_by', $request->received_by);
        }

        // Ordenar por fecha de creación (más recientes primero)
        $query->latest();

        $payments = $query->paginate($request->get('per_page', 15));

        return response()->json($payments, 200);
    }

    /**
     * Registrar un nuevo pago/abono
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sterilization_id' => 'required|exists:sterilizations,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,transfer,card,other',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($validated) {
            $sterilization = Sterilization::findOrFail($validated['sterilization_id']);

            // Verificar que el monto no exceda el saldo pendiente
            if ($validated['amount'] > $sterilization->balance) {
                return response()->json([
                    'message' => 'El monto del pago excede el saldo pendiente',
                    'balance' => $sterilization->balance,
                ], 422);
            }

            // Crear el pago
            $payment = Payment::create([
                ...$validated,
                'received_by' => Auth::id(),
            ]);

            // El modelo Payment automáticamente actualiza el estado de pago de la esterilización
            // mediante el observer en el método boot()

            $payment->load([
                'sterilization:id,campaign_id,owner_full_name,pet_name,total_price,total_paid,balance,payment_status',
                'sterilization.campaign:id,name',
                'receivedBy:id,name'
            ]);

            return response()->json([
                'message' => 'Pago registrado exitosamente',
                'payment' => $payment,
            ], 201);
        });
    }

    /**
     * Mostrar un pago específico
     * 
     * @param Payment $payment
     * @return JsonResponse
     */
    public function show(Payment $payment): JsonResponse
    {
        $payment->load([
            'sterilization:id,campaign_id,owner_full_name,owner_id_number,pet_name,total_price,total_paid,balance',
            'sterilization.campaign:id,name,location',
            'receivedBy:id,name,email'
        ]);

        return response()->json($payment, 200);
    }

    /**
     * Actualizar un pago
     * 
     * @param Request $request
     * @param Payment $payment
     * @return JsonResponse
     */
    public function update(Request $request, Payment $payment): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'sometimes|required|numeric|min:0.01',
            'payment_method' => 'sometimes|required|in:cash,transfer,card,other',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($validated, $payment) {
            // Si se modifica el monto, verificar que no exceda el saldo
            if (isset($validated['amount'])) {
                $sterilization = $payment->sterilization;
                $previousAmount = $payment->amount;
                $difference = $validated['amount'] - $previousAmount;
                
                if ($difference > $sterilization->balance) {
                    return response()->json([
                        'message' => 'El nuevo monto excedería el saldo pendiente',
                        'current_balance' => $sterilization->balance,
                    ], 422);
                }
            }

            $payment->update($validated);

            // El observer actualiza automáticamente el estado de pago
            $payment->load([
                'sterilization:id,campaign_id,owner_full_name,pet_name,total_price,total_paid,balance,payment_status',
                'sterilization.campaign:id,name',
                'receivedBy:id,name'
            ]);

            return response()->json([
                'message' => 'Pago actualizado exitosamente',
                'payment' => $payment,
            ], 200);
        });
    }

    /**
     * Eliminar un pago (soft delete)
     * 
     * @param Payment $payment
     * @return JsonResponse
     */
    public function destroy(Payment $payment): JsonResponse
    {
        $payment->delete();

        // El observer actualiza automáticamente el estado de pago de la esterilización

        return response()->json([
            'message' => 'Pago eliminado exitosamente',
        ], 200);
    }

    /**
     * Obtener estadísticas de pagos
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function statistics(Request $request): JsonResponse
    {
        $query = Payment::query();

        // Filtro opcional por rango de fechas
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->inDateRange($request->start_date, $request->end_date);
        }

        $stats = [
            'total_payments' => $query->count(),
            'total_amount' => $query->sum('amount'),
            'by_method' => [
                'cash' => (clone $query)->byMethod(Payment::METHOD_CASH)->sum('amount'),
                'transfer' => (clone $query)->byMethod(Payment::METHOD_TRANSFER)->sum('amount'),
                'card' => (clone $query)->byMethod(Payment::METHOD_CARD)->sum('amount'),
                'other' => (clone $query)->byMethod(Payment::METHOD_OTHER)->sum('amount'),
            ],
        ];

        return response()->json($stats, 200);
    }

    /**
     * Obtener historial de pagos de una esterilización
     * 
     * @param Sterilization $sterilization
     * @return JsonResponse
     */
    public function bySterilization(Sterilization $sterilization): JsonResponse
    {
        $payments = $sterilization->payments()
            ->with('receivedBy:id,name')
            ->latest()
            ->get();

        return response()->json([
            'sterilization' => [
                'id' => $sterilization->id,
                'owner_full_name' => $sterilization->owner_full_name,
                'pet_name' => $sterilization->pet_name,
                'total_price' => $sterilization->total_price,
                'total_paid' => $sterilization->total_paid,
                'balance' => $sterilization->balance,
                'payment_status' => $sterilization->payment_status,
            ],
            'payments' => $payments,
        ], 200);
    }
}
