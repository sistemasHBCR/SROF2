<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $guard = null): Response
    {
        if (Auth::guard($guard)->check()) {
            //Si el usuario está autenticado, bloquear el acceso a vista login. En su lugar lo rediriremos a esta.
            return redirect('/utilities-periods'); // Cambiar ' return redirect('/')' por la ruta a redirigir
        }

        return $next($request);
    }
}
