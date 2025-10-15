<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo de Esterilización
 * 
 * Gestiona la información de las esterilizaciones registradas,
 * incluyendo datos del propietario, mascota y estado de pago
 */
class Sterilization extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Los atributos que son asignables en masa
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'campaign_id',
        'owner_full_name',
        'owner_id_number',
        'owner_phone',
        'owner_email',
        'owner_address',
        'pet_name',
        'pet_type',
        'pet_gender',
        'pet_breed',
        'pet_age_months',
        'pet_weight',
        'total_price',
        'total_paid',
        'balance',
        'payment_status',
        'scheduled_date',
        'scheduled_time',
        'status',
        'notes',
        'registered_by',
    ];

    /**
     * Los atributos que deben ser casteados
     *
     * @var array<string, string>
     */
    protected $casts = [
        'pet_age_months' => 'integer',
        'pet_weight' => 'decimal:2',
        'total_price' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'balance' => 'decimal:2',
        'scheduled_date' => 'date',
        'scheduled_time' => 'datetime',
    ];

    /**
     * Constantes para tipos de mascotas
     */
    public const PET_TYPE_DOG = 'dog';
    public const PET_TYPE_CAT = 'cat';

    /**
     * Constantes para género de mascotas
     */
    public const PET_GENDER_MALE = 'male';
    public const PET_GENDER_FEMALE = 'female';

    /**
     * Constantes para estados de pago
     */
    public const PAYMENT_STATUS_PENDING = 'pending';
    public const PAYMENT_STATUS_PARTIAL = 'partial';
    public const PAYMENT_STATUS_COMPLETED = 'completed';

    /**
     * Constantes para estados de esterilización
     */
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Relación con la campaña
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Relación con el usuario que registró
     */
    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    /**
     * Relación con los pagos
     * Una esterilización puede tener múltiples pagos
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Calcular el saldo pendiente
     */
    public function calculateBalance(): float
    {
        return max(0, $this->total_price - $this->total_paid);
    }

    /**
     * Actualizar el estado de pago basado en el total pagado
     */
    public function updatePaymentStatus(): void
    {
        $this->total_paid = $this->payments()->sum('amount');
        $this->balance = $this->calculateBalance();

        if ($this->total_paid >= $this->total_price) {
            $this->payment_status = self::PAYMENT_STATUS_COMPLETED;
        } elseif ($this->total_paid > 0) {
            $this->payment_status = self::PAYMENT_STATUS_PARTIAL;
        } else {
            $this->payment_status = self::PAYMENT_STATUS_PENDING;
        }

        $this->save();
    }

    /**
     * Verificar si el pago está completo
     */
    public function isFullyPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_STATUS_COMPLETED;
    }

    /**
     * Verificar si la esterilización está completada
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Scope para esterilizaciones por campaña
     */
    public function scopeByCampaign($query, int $campaignId)
    {
        return $query->where('campaign_id', $campaignId);
    }

    /**
     * Scope para esterilizaciones por estado de pago
     */
    public function scopeByPaymentStatus($query, string $status)
    {
        return $query->where('payment_status', $status);
    }

    /**
     * Scope para esterilizaciones pendientes de pago
     */
    public function scopePendingPayment($query)
    {
        return $query->whereIn('payment_status', [
            self::PAYMENT_STATUS_PENDING,
            self::PAYMENT_STATUS_PARTIAL
        ]);
    }

    /**
     * Scope para esterilizaciones por propietario (cédula)
     */
    public function scopeByOwner($query, string $idNumber)
    {
        return $query->where('owner_id_number', $idNumber);
    }

    /**
     * Scope para esterilizaciones agendadas
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', self::STATUS_SCHEDULED);
    }

    /**
     * Obtener el nombre completo de la mascota con tipo
     */
    public function getFullPetNameAttribute(): string
    {
        $type = $this->pet_type === self::PET_TYPE_DOG ? 'Perro' : 'Gato';
        return "{$this->pet_name} ({$type})";
    }
}
