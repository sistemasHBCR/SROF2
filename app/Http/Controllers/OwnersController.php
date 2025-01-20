<?php

namespace App\Http\Controllers;

use App\Traits\LogsActivity;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\owner;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class OwnersController extends Controller  implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('owners.index'), only: ['index', 'edit']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('owners.edit'), only: ['update']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('owners.create'), only: ['store']),

        ];
    }
    use LogsActivity;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $owners = Owner::orderby('name')->get();
        return view('owners', compact('owners'));
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
            'name' => 'required|string|max:100|unique:owners',
            'email' => 'nullable|email',
            'active' => 'required',
        ]);

        DB::beginTransaction(); // Iniciar una transacción de base de datos
        try {

            $owner = Owner::create([
                'name' => $request->name,
                'email' => $request->email,
                'active' =>  $request->active,
            ]);

            // Registrar la actividad
            $this->logActivity(
                'create',
                'Utilities',
                'Dueños',
                'Creo un nuevo registro',
                'owners',
                null,
                $owner
            );

            DB::commit();
            return response()->json(['message' => 'Dueño agregado correctamente', 'owner' => $owner], 201);
        } catch (\Throwable $th) {
            DB::rollback(); // Revertir la transacción si hay algún error
            return response()->json(['error' => 'Problemas para agregar datos del dueño ' . $th->getMessage()], 500);
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
        $owner = owner::where('id', '=', $id)->get();

        if (!$owner) {
            return response()->json(['error' => 'Dueño no encontrado'], 404);
        }
        return response()->json(['owner' => $owner]);
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
                'max:100',
                Rule::unique('owners')->ignore($id),
            ],
            'email' => 'nullable|email',
            'active' => 'required',
        ]);

        DB::beginTransaction(); // Iniciar una transacción de base de datos
        try {

            // Buscar la residencia por el ID
            $owner = owner::findOrFail($id);
            $before = clone  $owner;  //capturar modelo antes de actualizar
            $owner->name = $request->name;
            $owner->email = $request->email;
            $owner->active = $request->active;
            $owner->save();
            $owner->refresh();

            // Registrar la actividad
            $this->logActivity(
                'update',
                'Utilities',
                'Dueños',
                'Actualizo un registro',
                'owners',
                $before,
                $owner
            );


            DB::commit();
            return response()->json(['message' => 'Dueño actualizado con exito', 'owner' => $owner], 200);
        } catch (\Throwable $th) {
            DB::rollback(); // Revertir la transacción si hay algún error
            return response()->json(['error' => 'Problemas para actualizar datos del dueño ' . $th->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
