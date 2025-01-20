<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\LogsActivity;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class RolesController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('roles.index'), only: ['index', 'edit']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('roles.edit'), only: ['update']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('roles.create'), only: ['store']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('roles.destroy'), only: ['destroy']),
        ];
    }

    use LogsActivity;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $admins = Role::with('users')->where('name', 'Administrador')->first();
        $suspends = Role::with('users')->where('name', 'Suspendido')->first();
        $roles = Role::with('users')->whereNotIn('name', ['Administrador', 'Suspendido'])->get();
        $permissions = Permission::all();

        return view('roles', compact('admins', 'roles', 'suspends', 'permissions'));
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
        // Validación
        $validatedData = $request->validate([
            'name' => 'required|unique:roles|max:255',
        ]);
        DB::beginTransaction(); // Iniciar una transacción de base de datos
        try {

            $colores = ['#FF9A08', '#800080', '#FF00FF', '#000080', '#0000FF', '#008080', '#00FFFF', '#008000', '#00FF00', '#FFFF00', '#800000', '#FF0000'];
            $colores_usados = Role::pluck('color')->toArray();
            $colores_disponibles = array_diff($colores, $colores_usados);
            if (!empty($colores_disponibles)) {
                $color = $colores_disponibles[array_rand($colores_disponibles)];
            } else {
                $color = '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
            }

            // Crear el nuevo rol
            $role = Role::create([
                'name' => $validatedData['name'],
                'color' => $color
            ]);

            //asociar permisos del rol
            $role->permissions()->sync($request->permissions);



            //***Registrar la actividad**/

            // Obtener los nombres de los permisos asociados al rol
            $permissionsNames = $role->permissions()->pluck('name')->toArray();
            // Agregar los nombres de permisos al objeto $role
            $role->permissions_names = $permissionsNames;

            $this->logActivity(
                'create',
                'Utilities',
                'Roles',
                'Creo un nuevo registro',
                'roles',
                null,
                $role
            );

            DB::commit();
            return response()->json(['message' => 'Rol creado con exito']);
        } catch (\Throwable $th) {
            DB::rollback(); // Revertir la transacción si hay algún error
            // Si hay una excepción, no guardar los cambios y devolver un mensaje de error.
            return response()->json(['error' => 'Problemas para crear este rol ' . $th->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $role = Role::where('id', $id)->first();
            $permissions = $role->permissions;
            return response()->json(['role' => $role, 'permissions' => $permissions]);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Problemas para leer este rol ' . $th->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles')->ignore($id),
            ],
        ]);

        DB::beginTransaction(); // Iniciar una transacción de base de datos
        try {
            // Buscar el rol por el ID
            $role = Role::findOrFail($id);

            // Capturar datos del modelo antes de actualizar
            $permissionsNamesBefore = $role->permissions()->pluck('name')->toArray();
            $before = [
                'role' => $role->toArray(),
                'permissions' => $permissionsNamesBefore,
            ];

            // Actualizar los campos con los nuevos valores
            $role->name = $request->name;
            $role->save();
            $role->refresh();

            // Actualizar permisos del rol
            $role->permissions()->sync($request->permissions);
            $role->load('permissions'); // Refrescar relación

            // Capturar datos del modelo después de actualizar
            $permissionsNamesAfter = $role->permissions()->pluck('name')->toArray();
            $after = [
                'role' => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'color' => $role->color,
                    'guard_name' => $role->guard_name,
                    'created_at' => $role->created_at,
                    'updated_at' => $role->updated_at,
                ],
                'permissions' => $permissionsNamesAfter,
            ];


            // Registrar la actividad
            $this->logActivity(
                'update',
                'Utilities',
                'Roles',
                'Actualizó un registro',
                'roles',
                $before,
                $after
            );

            DB::commit();
            return response()->json(['message' => 'Rol actualizado con éxito', 'role' => $role], 200);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['error' => 'Problemas para actualizar este rol: ' . $th->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::beginTransaction(); // Iniciar una transacción de base de datos
        try {

            $role = Role::with('users')->findOrFail($id);

            // Verifica si el ID del rol no es ni 1 ni 2
            if ($role->id != 1 && $role->id != 2) {

                //capturar datos antes de eliminar
                $usersNamesBefore = $role->users()->pluck('username')->toArray();
                $before = [
                    'role' => [
                        'id' => $role->id,
                        'name' => $role->name,
                        'color' => $role->color,
                        'guard_name' => $role->guard_name,
                        'created_at' => $role->created_at,
                        'updated_at' => $role->updated_at,
                    ],
                    'users' => $usersNamesBefore,
                ];


                // Elimina el rol
                $role->delete();

                // Registrar la actividad

                $this->logActivity(
                    'delete',
                    'Utilities',
                    'Roles',
                    'Elimino un registro',
                    'roles',
                    $before,
                    null
                );

                DB::commit();
                return response()->json(['message' => '¡Rol eliminado correctamente!'], 200);
            } else {
                return response()->json(['message' => 'No se puede eliminar este rol.'], 403);
            }
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => 'Error al eliminar el rol: ' . $th->getMessage()], 500);
        }
    }
}
