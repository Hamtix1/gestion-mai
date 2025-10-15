<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo para gestionar los roles del sistema
 * 
 * Roles disponibles:
 * - admin: Administrador del sistema
 * - seller: Vendedor de esterilizaciones
 * - user: Usuario público/visitante
 */
class Role extends Model
{
    use HasFactory;

    /**
     * Los atributos que son asignables en masa
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'display_name',
        'description',
    ];

    /**
     * Constantes para los nombres de roles
     */
    public const ADMIN = 'admin';
    public const SELLER = 'seller';
    public const USER = 'user';

    /**
     * Relación con usuarios
     * Un rol puede tener muchos usuarios
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Verifica si el rol es de administrador
     */
    public function isAdmin(): bool
    {
        return $this->name === self::ADMIN;
    }

    /**
     * Verifica si el rol es de vendedor
     */
    public function isSeller(): bool
    {
        return $this->name === self::SELLER;
    }
}
