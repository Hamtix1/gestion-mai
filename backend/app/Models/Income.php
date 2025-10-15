<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo de Ingreso
 * 
 * Gestiona los ingresos del movimiento, incluyendo pagos de esterilizaciones,
 * donaciones, recaudaciones de fondos, etc.
 */
class Income extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Los atributos que son asignables en masa
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'campaign_id',
        'concept',
        'description',
        'amount',
        'source',
        'income_date',
        'reference_number',
        'registered_by',
    ];

    /**
     * Los atributos que deben ser casteados
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'income_date' => 'date',
    ];

    /**
     * Constantes para fuentes de ingreso
     */
    public const SOURCE_STERILIZATION = 'sterilization';
    public const SOURCE_DONATION = 'donation';
    public const SOURCE_FUNDRAISING = 'fundraising';
    public const SOURCE_OTHER = 'other';

    /**
     * Relación con la campaña
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Relación con el usuario que registró el ingreso
     */
    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    /**
     * Scope para ingresos por campaña
     */
    public function scopeByCampaign($query, int $campaignId)
    {
        return $query->where('campaign_id', $campaignId);
    }

    /**
     * Scope para ingresos por fuente
     */
    public function scopeBySource($query, string $source)
    {
        return $query->where('source', $source);
    }

    /**
     * Scope para ingresos en un rango de fechas
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('income_date', [$startDate, $endDate]);
    }

    /**
     * Obtener el nombre de la fuente en español
     */
    public function getSourceNameAttribute(): string
    {
        return match($this->source) {
            self::SOURCE_STERILIZATION => 'Esterilización',
            self::SOURCE_DONATION => 'Donación',
            self::SOURCE_FUNDRAISING => 'Recaudación de Fondos',
            self::SOURCE_OTHER => 'Otro',
            default => 'Desconocido',
        };
    }
}
