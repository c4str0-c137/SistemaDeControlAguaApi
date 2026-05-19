<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if (!$user || !$user->role) {
            return response()->json(['message' => 'Acceso denegado. Rol no asignado.'], 403);
        }

        $userRole = $user->role->name;

        // Case-insensitive comparison
        foreach ($roles as $role) {
            if (strtolower($userRole) === strtolower($role)) {
                return $next($request);
            }
        }

        return response()->json([
            'message' => 'Acceso denegado. Requiere rol: ' . implode(', ', $roles)
        ], 403);
    }
}
