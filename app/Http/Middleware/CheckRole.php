<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware para verificar rol de usuario
 * 
 * Uso en rutas:
 * Route::get('/admin', [AdminController::class, 'index'])->middleware('role:administrador');
 * Route::get('/multi', [Controller::class, 'index'])->middleware('role:administrador,instructor');
 */
class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'No autenticado'
            ], 401);
        }

        $userRole = $user->rol?->slug;

        if (!in_array($userRole, $roles)) {
            return response()->json([
                'message' => 'No tienes el rol necesario para acceder a este recurso',
                'roles_required' => $roles,
                'your_role' => $userRole
            ], 403);
        }

        return $next($request);
    }
}
