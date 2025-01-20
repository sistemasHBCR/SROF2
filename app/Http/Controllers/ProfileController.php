<?php

namespace App\Http\Controllers;

use App\Traits\LogsActivity;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;


class ProfileController extends Controller implements HasMiddleware
{

    use LogsActivity;

    public static function middleware(): array
    {
        return [
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('profile.details'), only: ['update']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('profile.desactivate'), only: ['desactive']),

        ];
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }
    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Captura los datos relevantes antes de la actualización
        $before = $user->only(['id', 'name', 'last_name', 'email', 'username', 'avatar_class']);

        // Rellena los datos del usuario con los datos validados de la solicitud
        $user->fill($request->validated());

        // Si el campo 'email' ha cambiado, marca el 'email_verified_at' como nulo
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        DB::beginTransaction(); // Iniciar una transacción de base de datos
        try {
            // Guarda los datos actualizados
            $success = $user->save();

            // Captura los datos relevantes después de la actualización
            $after = $user->only(['id', 'name', 'last_name', 'email', 'username', 'avatar_class']);

            // Registrar la actividad
            $this->logActivity(
                'update',
                'Utilities',
                'Profile',
                'Actualizó datos generales',
                'users',
                $before,
                $after
            );

            DB::commit();
            return Redirect::route('profile.edit')->with('status', 'Datos actualizados con éxito');
        } catch (\Throwable $th) {
            DB::rollback();
            return Redirect::route('profile.edit')->with('status', 'Problemas para actualizar');
        }
    }


    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Desactive the user's account.
     */
    public function desactive(Request $request)
    {
        $user = $request->user();
        
        // Captura los nombres de roles antes de la desactivación
        $beforeRoles = array_map(function ($role) {
            return $role['name'];
        }, $user->roles->toArray()); // Convertir la colección de roles a un array
    
        $before = [
            'Roles' => $beforeRoles,
        ];
    
        // Sincroniza los roles para establecer el nuevo rol 'suspendido'
        $user->syncRoles(['suspendido']);
    
        // Captura los nombres de roles después de la desactivación
       // Después de la sincronización, el único rol será 'suspendido'
        $after = [
            'Roles' => ['Suspendido'],
        ];
    
        // Registrar la actividad
        $this->logActivity(
            'update',
            'Utilities',
            'Profile',
            'Suspendió de forma manual su cuenta de usuario',
            'users',
            $before,
            $after
        );
    
        // Realiza el logout del usuario
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('home')->with('status', 'Su cuenta ha sido suspendida.');
    }
    
}
