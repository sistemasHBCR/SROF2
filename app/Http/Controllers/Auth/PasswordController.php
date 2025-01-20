<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Traits\LogsActivity;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    use LogsActivity;

    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {

        try {

            /*PROFILE*/
            if ($request->filled('current_password')) {

                $validated = $request->validateWithBag('updatePassword', [
                    'current_password' => ['required', 'current_password'],
                    'password' => ['required', Password::defaults(), 'confirmed'],
                ]);

                // Si la validación del 'current_password' es correcta, actualizar la contraseña
                $request->user()->update([
                    'password' => Hash::make($validated['password']),
                    'change_next_login' => 'N',
                ]);

                // Registrar la actividad
                $this->logActivity(
                    'update',
                    'Utilities',
                    'Profile',
                    'Se actualizo contraseña de sesión',
                    'users',
                    null,
                    null
                );

                return back()->with('password-updated', 'Contraseña actualizada con éxito');
            } else {

                /*FORZAR CAMBIO CONTRASEÑA*/
                $validated = $request->validateWithBag('updatePassword', [
                    'password' => ['required', Password::defaults(), 'confirmed'],
                ]);

                $request->user()->update([
                    'password' => Hash::make($validated['password']),
                    'change_next_login' => 'N',
                ]);


                // Registrar la actividad
                $this->logActivity(
                    'update',
                    'Utilities',
                    'Auth',
                    'Se actualizo contraseña de sesión',
                    'users',
                    null,
                    null
                );
                return redirect()->route('utilities-periods');
            }
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
