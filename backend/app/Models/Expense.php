<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo de Egreso
 * 
 * Gestiona los gastos del movimiento, incluyendo costos médicos,
 * transporte, suministros, marketing, etc.
 */
class Expense extends Model
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
        'category',
        'expense_date',
        'invoice_number',
        'supplier',
        'registered_by',
    ];

    /**
     * Los atributos que deben ser casteados
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
    ];

    /**
     * Constantes para categorías de gasto
     */
    public const CATEGORY_MEDICAL = 'medical';
    public const CATEGORY_TRANSPORTATION = 'transportation';
    public const CATEGORY_SUPPLIES = 'supplies';
    public const CATEGORY_MARKETING = 'marketing';
    public const CATEGORY_ADMINISTRATIVE = 'administrative';
    public const CATEGORY_OTHER = 'other';

    /**
     * Relación con la campaña
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Relación con el usuario que registró el egreso
     */
    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    /**
     * Scope para egresos por campaña
     */
    public function scopeByCampaign($query, int $campaignId)
    {
        return $query->where('campaign_id', $campaignId);
    }

    /**
     * Scope para egresos por categoría
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope para egresos en un rango de fechas
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('expense_date', [$startDate, $endDate]);
    }

    /**
     * Obtener el nombre de la categoría en español
     */
    public function getCategoryNameAttribute(): string
    {
        return match($this->category) {
            self::CATEGORY_MEDICAL => 'Médico',
            self::CATEGORY_TRANSPORTATION => 'Transporte',
            self::CATEGORY_SUPPLIES => 'Suministros',
            self::CATEGORY_MARKETING => 'Marketing',
            self::CATEGORY_ADMINISTRATIVE => 'Administrativo',
            self::CATEGORY_OTHER => 'Otro',
            default => 'Desconocido',
        };
    }
}
