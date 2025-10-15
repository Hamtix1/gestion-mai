<?php

namespace App\Http\Middleware;

use App\Models\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware para verificar que el usuario sea Vendedor o Administrador
 */
class EnsureUserIsSeller
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || (!$user->isSeller() && !$user->isAdmin())) {
            return response()->json([
                'message' => 'No tienes permisos para acceder a este recurso. Se requiere rol de vendedor o administrador.',
            ], 403);
        }

        return $next($request);
    }
}
