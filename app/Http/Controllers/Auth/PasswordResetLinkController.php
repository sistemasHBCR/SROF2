<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Traits\SendsAzurePasswordResetEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;
use App\Models\User; // Asegúrate de importar el modelo User

class PasswordResetLinkController extends Controller
{
    use SendsAzurePasswordResetEmail;

    public function __construct()
    {
        $this->initializeSendsAzurePasswordResetEmail();
    }

    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Obtener el usuario por correo electrónico
        $user = User::where('email', $request->email)->first();

        if ($user) {
            // Generar el token de restablecimiento de contraseña
            $token = Password::broker()->createToken($user);

            // Enviar el correo de restablecimiento de contraseña a través de Azure
            $this->sendPasswordResetEmail($request->email, $token);
            return back()->with('status', __('We have emailed your password reset link!'));
        } else {
            return back()->withInput($request->only('email'))
                         ->withErrors(['email' => __('Unable to find a user with that email address.')]);
        }
    }
}
