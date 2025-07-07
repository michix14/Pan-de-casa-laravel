<?php

// app/Http/Middleware/RoleMiddleware.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Maneja la petición.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed  ...$roles  // roles pasados como parámetros
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        // Recorremos los roles recibidos y salimos si el usuario coincide con alguno
        foreach ($roles as $role) {
            // Ej.: campo "is_gerente", "is_cajero" en la tabla users
            if ($user && $user->{"is_$role"}) {
                return $next($request);
            }
        }

        abort(403, 'No tienes permiso para acceder a esta ruta.');
    }
}
