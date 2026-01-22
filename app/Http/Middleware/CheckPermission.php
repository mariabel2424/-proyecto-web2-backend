<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware para verificar permisos de usuario
 * 
 * Uso en rutas:
 * Route::get('/cursos', [CursoController::class, 'index'])->middleware('permission:cursos.ver');
 */
class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'No autenticado'
            ], 401);
        }

        // Administrador tiene todos los permisos
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Verificar si el usuario tiene el permiso
        if (!$user->hasPermission($permission)) {
            return response()->json([
                'message' => 'No tienes permiso para realizar esta acción',
                'permission_required' => $permission
            ], 403);
        }

        return $next($request);
    }
}
