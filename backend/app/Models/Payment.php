<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo de Pago
 * 
 * Gestiona los pagos/abonos realizados para las esterilizaciones
 * Permite pagos parciales y múltiples abonos
 * Crea automáticamente ingresos cuando la esterilización está completamente pagada
 */
class Payment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Los atributos que son asignables en masa
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sterilization_id',
        'amount',
        'payment_method',
        'reference_number',
        'notes',
        'received_by',
    ];

    /**
     * Los atributos que deben ser casteados
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Constantes para métodos de pago
     */
    public const METHOD_CASH = 'cash';
    public const METHOD_TRANSFER = 'transfer';
    public const METHOD_CARD = 'card';
    public const METHOD_OTHER = 'other';

    /**
     * Relación con la esterilización
     */
    public function sterilization(): BelongsTo
    {
        return $this->belongsTo(Sterilization::class);
    }

    /**
     * Relación con el usuario que recibió el pago
     */
    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Boot del modelo para actualizar automáticamente el estado de pago
     */
    protected static function boot()
    {
        parent::boot();

        // Después de crear un pago, actualizar el estado de la esterilización
        static::created(function ($payment) {
            $payment->sterilization->updatePaymentStatus();
            
            // Si la esterilización está completamente pagada, crear ingreso
            static::createIncomeIfFullyPaid($payment->sterilization);
        });

        // Después de actualizar un pago, actualizar el estado de la esterilización
        static::updated(function ($payment) {
            $payment->sterilization->updatePaymentStatus();
            
            // Si la esterilización está completamente pagada, crear ingreso
            static::createIncomeIfFullyPaid($payment->sterilization);
        });

        // Después de eliminar un pago, actualizar el estado de la esterilización
        static::deleted(function ($payment) {
            $payment->sterilization->updatePaymentStatus();
            
            // Si existe un ingreso asociado y ya no está pagado, eliminarlo
            static::deleteIncomeIfNotPaid($payment->sterilization);
        });
    }

    /**
     * Crear ingreso automáticamente si la esterilización está completamente pagada
     */
    protected static function createIncomeIfFullyPaid(Sterilization $sterilization): void
    {
        // Solo crear ingreso si está completamente pagado
        if ($sterilization->payment_status !== Sterilization::PAYMENT_STATUS_COMPLETED) {
            return;
        }

        // Verificar si ya existe un ingreso para esta esterilización
        $existingIncome = Income::where('reference_number', 'STER-' . $sterilization->id)->first();
        
        if ($existingIncome) {
            // Actualizar el monto si ya existe
            $existingIncome->update([
                'amount' => $sterilization->total_paid,
            ]);
            return;
        }

        // Crear nuevo ingreso
        Income::create([
            'campaign_id' => $sterilization->campaign_id,
            'concept' => 'Esterilización #' . $sterilization->id . ' - ' . $sterilization->pet_name,
            'description' => 'Ingreso por esterilización de ' . $sterilization->pet_name . ' (Propietario: ' . $sterilization->owner_full_name . ')',
            'amount' => $sterilization->total_paid,
            'source' => 'sterilization',
            'income_date' => $sterilization->scheduled_date ?? now()->toDateString(),
            'reference_number' => 'STER-' . $sterilization->id,
            'registered_by' => auth()->id() ?? 1, // Usuario autenticado o admin por defecto
        ]);
    }

    /**
     * Eliminar ingreso si la esterilización ya no está pagada
     */
    protected static function deleteIncomeIfNotPaid(Sterilization $sterilization): void
    {
        // Si ya no está completamente pagado, eliminar el ingreso asociado
        if ($sterilization->payment_status === Sterilization::PAYMENT_STATUS_COMPLETED) {
            return;
        }

        Income::where('reference_number', 'STER-' . $sterilization->id)->delete();
    }

    /**
     * Scope para pagos por método
     */
    public function scopeByMethod($query, string $method)
    {
        return $query->where('payment_method', $method);
    }

    /**
     * Scope para pagos en un rango de fechas
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Obtener el nombre del método de pago en español
     */
    public function getPaymentMethodNameAttribute(): string
    {
        return match($this->payment_method) {
            self::METHOD_CASH => 'Efectivo',
            self::METHOD_TRANSFER => 'Transferencia',
            self::METHOD_CARD => 'Tarjeta',
            self::METHOD_OTHER => 'Otro',
            default => 'Desconocido',
        };
    }
}
