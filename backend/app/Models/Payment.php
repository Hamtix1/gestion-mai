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
        });

        // Después de eliminar un pago, actualizar el estado de la esterilización
        static::deleted(function ($payment) {
            $payment->sterilization->updatePaymentStatus();
        });
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
