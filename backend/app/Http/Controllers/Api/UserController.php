<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Controlador de Usuarios
 * 
 * Gestiona los usuarios del sistema (solo para admin)
 */
class UserController extends Controller
{
    /**
     * Listar todos los usuarios
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::with('role:id,name,display_name');

        // Filtro por rol
        if ($request->has('role')) {
            $query->byRole($request->role);
        }

        // Filtro por estado activo
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Búsqueda por nombre o email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json($users, 200);
    }

    /**
     * Mostrar un usuario específico
     * 
     * @param User $user
     * @return JsonResponse
     */
    public function show(User $user): JsonResponse
    {
        $user->load([
            'role:id,name,display_name',
            'createdCampaigns:id,name,status',
            'registeredSterilizations:id,owner_full_name,pet_name,status',
        ]);

        return response()->json($user, 200);
    }

    /**
     * Crear un nuevo usuario
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'sometimes|boolean',
        ]);

        $user = User::create([
            ...$validated,
            'password' => Hash::make($validated['password']),
        ]);

        $user->load('role:id,name,display_name');

        return response()->json([
            'message' => 'Usuario creado exitosamente',
            'user' => $user,
        ], 201);
    }

    /**
     * Actualizar un usuario
     * 
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|required|string|min:8',
            'role_id' => 'sometimes|required|exists:roles,id',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'sometimes|boolean',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);
        $user->load('role:id,name,display_name');

        return response()->json([
            'message' => 'Usuario actualizado exitosamente',
            'user' => $user,
        ], 200);
    }

    /**
     * Eliminar un usuario (soft delete)
     * 
     * @param User $user
     * @return JsonResponse
     */
    public function destroy(User $user): JsonResponse
    {
        // No permitir eliminar al propio usuario
        if ($user->id === auth()->id()) {
            return response()->json([
                'message' => 'No puedes eliminar tu propia cuenta',
            ], 422);
        }

        // Verificar si tiene registros asociados
        $hasRecords = $user->createdCampaigns()->count() > 0 ||
                      $user->registeredSterilizations()->count() > 0 ||
                      $user->receivedPayments()->count() > 0;

        if ($hasRecords) {
            // En lugar de eliminar, desactivar
            $user->update(['is_active' => false]);
            
            return response()->json([
                'message' => 'El usuario ha sido desactivado debido a que tiene registros asociados',
            ], 200);
        }

        $user->delete();

        return response()->json([
            'message' => 'Usuario eliminado exitosamente',
        ], 200);
    }

    /**
     * Activar/desactivar un usuario
     * 
     * @param User $user
     * @return JsonResponse
     */
    public function toggleStatus(User $user): JsonResponse
    {
        // No permitir desactivar al propio usuario
        if ($user->id === auth()->id()) {
            return response()->json([
                'message' => 'No puedes cambiar el estado de tu propia cuenta',
            ], 422);
        }

        $user->update(['is_active' => !$user->is_active]);

        return response()->json([
            'message' => $user->is_active 
                ? 'Usuario activado exitosamente' 
                : 'Usuario desactivado exitosamente',
            'user' => $user,
        ], 200);
    }
}
