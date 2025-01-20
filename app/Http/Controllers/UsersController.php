<?php

namespace App\Http\Controllers;

use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;


class UsersController extends Controller implements HasMiddleware
{
    use LogsActivity;
    public static function middleware(): array
    {
        return [
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('users.index'), only: ['index', 'edit']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('users.edit'), only: ['update']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('users.create'), only: ['store']),

        ];
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::all();
        $users = User::with('roles')->get();
        return view('users', compact('users', 'roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $request->validate([
            'name' => ['required', 'string', 'max:20'],
            'last_name' => ['required', 'string', 'max:20'],
            'username' => ['required', 'string', 'min:6', 'max:20', 'unique:' . User::class],
            'roles' => ['required'],
            'email' => ['nullable', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        DB::beginTransaction(); // Iniciar una transacción de base de datos
        try {

            $clases = ['success', 'primary', 'secondary', 'info', 'danger', 'warning'];

            $user = User::create([
                'name' => $request->name,
                'last_name' => $request->last_name,
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'evatar_class' => $clases[array_rand($clases)],
            ]);


            //actualizar roles a usuario
            $user->syncRoles($request->roles);
            // Recargar la relación 'role' para obtener los datos actualizados
            $user->load('roles');


            // Registrar la actividad
            $this->logActivity(
                'create',
                'Utilities',
                'Usuarios',
                'Creo un nuevo registro',
                'users',
                null,
                $user
            );


            DB::commit();
            return response()->json([
                'message' => 'Usuario creado correctamente',
                'id' => $user->id,
                'name' => $user->name,
                'last_name' => $user->last_name,
                'username' => $user->username,
                'email' => $user->email,
                'avatar_class' => $user->avatar_class,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
                'roles' => $request->roles
            ], 201);
        } catch (\Throwable $th) {
            DB::rollback(); // Revertir la transacción si hay algún error
            return response()->json(['error' => 'Problemas para actualizar este usuario ' . $th->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {

        $user = User::where('id', '=', $id)->with('roles:name')->get();

        if (!$user) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }
        return response()->json(['user' => $user]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $request->validate([
            'name' => ['required', 'string', 'max:20'],
            'last_name' => ['required', 'string', 'max:20'],
            'username' => ['required', 'string', 'min:6', 'max:20',  Rule::unique('users')->ignore($id)],
            'roles' => ['required'],
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique('users')->ignore($id)],
            'password' => [
                function ($attribute, $value, $fail) use ($request) {
                    // Verificar si el checkbox está marcado
                    if ($request->filled('change_password') && $request->input('change_password') == 'Y') {
                        $request->validate([
                            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
                        ]);
                    }
                },
            ],
        ]);

        DB::beginTransaction(); // Iniciar una transacción de base de datos
        try {
            // Buscar el usuario por el ID
            $user = User::with('roles')->findOrFail($id);
            $before = clone $user;  //capturar modelo antes de actualizar
            // Actualizar los campos con los nuevos valores
            $user->name = $request->name;
            $user->last_name = $request->last_name;
            $user->username = $request->username;
            $user->email = $request->email;
            // Verificar si checkbox cambio de contraseña está marcado
            if ($request->filled('change_password') && $request->input('change_password') == 'Y') {
                $user->password = Hash::make($request->password);
                $user->change_next_login =  $request->change_next_login;
            }
            $user->save();

            //actualizar roles a usuario
            $user->syncRoles($request->roles);
            // Recargar la relación 'roles' para obtener los datos actualizados
            $user->load('roles');


            // Registrar la actividad
            $this->logActivity(
                'update',
                'Utilities',
                'Usuario',
                'Actualizo un registro',
                'users',
                $before,
                $user
            );


            DB::commit();
            return response()->json([
                'message' => 'Usuario actualizado con exito',
                'id' => $user->id,
                'name' => $user->name,
                'last_name' => $user->last_name,
                'username' => $user->username,
                'email' => $user->email,
                'avatar_class' => $user->avatar_class,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
                'roles' => $request->roles
            ], 200);

        } catch (\Throwable $th) {
            DB::rollback(); // Revertir la transacción si hay algún error
            return response()->json(['error' => 'Problemas para actualizar este usuario ' .  $th->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
    }

    //vista reset password despues del login
    public function resetpassword()
    {
        return view('auth.new-password');
    }
}
