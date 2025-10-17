<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo de Campaña de Esterilización
 * 
 * Gestiona las campañas de esterilización con información de ubicación,
 * fechas, cupos disponibles y precios
 */
class Campaign extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Los atributos que son asignables en masa
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'location',
        'available_slots',
        'used_slots',
        'dog_price',
        'cat_price',
        'status',
        'is_visible_to_public',
        'created_by',
    ];

    /**
     * Los atributos que deben ser casteados
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'available_slots' => 'integer',
        'used_slots' => 'integer',
        'dog_price' => 'decimal:2',
        'cat_price' => 'decimal:2',
        'is_visible_to_public' => 'boolean',
    ];

    /**
     * Constantes para estados de campaña
     */
    public const STATUS_PLANNED = 'planned';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Relación con el usuario creador
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relación con esterilizaciones
     * Una campaña puede tener muchas esterilizaciones
     */
    public function sterilizations(): HasMany
    {
        return $this->hasMany(Sterilization::class);
    }

    /**
     * Relación con ingresos
     */
    public function incomes(): HasMany
    {
        return $this->hasMany(Income::class);
    }

    /**
     * Relación con egresos
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Obtener cupos restantes
     */
    public function getRemainingSlots(): int
    {
        return max(0, $this->available_slots - $this->used_slots);
    }

    /**
     * Verificar si hay cupos disponibles
     */
    public function hasAvailableSlots(): bool
    {
        return $this->getRemainingSlots() > 0;
    }

    /**
     * Verificar si la campaña está activa
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Verificar si la campaña es visible al público
     */
    public function isVisibleToPublic(): bool
    {
        return $this->is_visible_to_public;
    }

    /**
     * Scope para campañas activas
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope para campañas visibles al público
     */
    public function scopeVisibleToPublic($query)
    {
        return $query->where('is_visible_to_public', true);
    }

    /**
     * Scope para campañas con cupos disponibles
     */
    public function scopeWithAvailableSlots($query)
    {
        return $query->whereColumn('used_slots', '<', 'available_slots');
    }

    /**
     * Scope para campañas en rango de fechas
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
              ->orWhereBetween('end_date', [$startDate, $endDate]);
        });
    }

    /**
     * Calcular total de ingresos de la campaña
     */
    public function getTotalIncome(): float
    {
        return $this->incomes()->sum('amount');
    }

    /**
     * Calcular total de egresos de la campaña
     */
    public function getTotalExpense(): float
    {
        return $this->expenses()->sum('amount');
    }

    /**
     * Calcular balance de la campaña
     */
    public function getBalance(): float
    {
        return $this->getTotalIncome() - $this->getTotalExpense();
    }

    /**
     * Calcular total recaudado de las esterilizaciones de la campaña
     * Suma el total_paid de todas las esterilizaciones
     */
    public function getTotalCollected(): float
    {
        return $this->sterilizations()->sum('total_paid');
    }

    /**
     * Calcular estadísticas de pagos de la campaña
     */
    public function getPaymentStats(): array
    {
        $sterilizations = $this->sterilizations;
        
        return [
            'completed_payments' => $sterilizations->where('payment_status', Sterilization::PAYMENT_STATUS_COMPLETED)->count(),
            'partial_payments' => $sterilizations->where('payment_status', Sterilization::PAYMENT_STATUS_PARTIAL)->count(),
            'pending_payments' => $sterilizations->where('payment_status', Sterilization::PAYMENT_STATUS_PENDING)->count(),
            'total_collected' => $sterilizations->sum('total_paid'),
            'total_expected' => $sterilizations->sum('total_price'),
        ];
    }
}
