<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Traits\LogsActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{

    use LogsActivity;

    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $this->logActivity(
            'Login',
            'Auth', 
            'Inicio de sesión', 
            'El usuario inició sesión', 
            null, 
            null, 
            [ // Datos después
                'username' => Auth::user()->username,
                'name' => Auth::user()->name . ' ' . Auth::user()->last_name,
                'email' => Auth::user()->email,
            ]
        );


        return redirect()->intended(route('utilities-periods', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {

        $this->logActivity(
            'Login',
            'Auth', 
            'Cierre de sesión', 
            'El usuario cerro sesión', 
            null, 
            null, 
            [ // Datos después
                'username' => Auth::user()->username,
                'name' => Auth::user()->name . ' ' . Auth::user()->last_name,
                'email' => Auth::user()->email,
            ]
        );
        
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

}
