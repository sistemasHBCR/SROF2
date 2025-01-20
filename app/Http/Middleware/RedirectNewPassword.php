<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RedirectNewPassword
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar si el usuario está autenticado y necesita cambiar la contraseña en el próximo inicio de sesión
        if (Auth::check() && Auth::user()->change_next_login === 'Y') {
            // Verificar si ya estamos en la página de reset de contraseña para evitar el ciclo de redirección
            if ($request->route()->getName() !== 'auth.change-password') {
                // Redirigir al usuario a la vista de reset de contraseña
                return redirect()->route('auth.change-password');
            }
        }

        return $next($request);
    }
}
