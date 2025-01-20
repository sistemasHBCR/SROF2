<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $user = Auth::user();

        if ($user) {
            // Si el usuario es 'Administrador', ignorar cualquier asignación de permiso
            if ($user->hasRole('Administrador')) {
                Gate::before(function ($user, $ability) {
                    return true;
                });
            }

            // Si el usuario es 'Suspendido', rechazar el acceso
            if ($user->hasRole('Suspendido') || $user->roles->isEmpty()) {
                abort(403, 'Tu cuenta está suspendida.');
            }
        }

        return $next($request);
    }
}
