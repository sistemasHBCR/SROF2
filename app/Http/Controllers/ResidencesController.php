<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\LogsActivity;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\residence;
use App\Models\owner;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ResidencesController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('residences.index'), only: ['index', 'edit']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('residences.edit'), only: ['update']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('residences.create'), only: ['store']),

        ];
    }

    use LogsActivity;


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $residences = Residence::with('owner')->orderby('name')->get();
        $owners = Owner::where('active', 'Y')->orderby('name')->get();

        
        return view('residences', compact('residences', 'owners'));
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
            'number' => 'required|integer|unique:residences',
            'name' => 'required|string|max:25|unique:residences',
            'owner' => 'required|array', // Validate owner as an array
            'owner.*' => 'required|string', // Validate each owner name as a string
            'active' => 'required',
        ]);

        DB::beginTransaction(); // Iniciar una transacción de base de datos
        try {
            $residence = Residence::create([
                'number' => $request->number,
                'name' => $request->name,
                'active' =>  $request->active,
            ]);

            // Actualizar owner de la residencia
            $residence->owner()->sync($request->owner);
            // Obtener names owners
            $owners = $residence->owner->pluck('name');
            $ownersArray = $owners->toArray();
            $limit = 3;
            if (count($ownersArray) > $limit) {
                $displayNames = array_slice($ownersArray, 0, $limit);
                $remainingCount = count($ownersArray) - $limit;
                $namesowners  = implode(' / ', $displayNames) . " y otros {$remainingCount} más...";
            } else {
                $namesowners  = implode(' / ', $ownersArray);
            }



            // Registrar la actividad
            $this->logActivity(
                'create',
                'Utilities',
                'Residencias',
                'Creo un nuevo registro',
                'residences',
                null,
                $residence
            );

            DB::commit();
            return response()->json(['message' => 'Residencia creada correctamente', 'residence' => $residence, 'owners' => $namesowners], 201);
        } catch (\Throwable $th) {
            DB::rollback(); // Revertir la transacción si hay algún error
            return response()->json(['error' => 'Problemas para crear esta residencia ' . $th->getMessage()], 500);
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

        $residence = Residence::where('id', '=', $id)->with('owner:id')->get();

        if (!$residence) {
            return response()->json(['error' => 'Residencia no encontrada'], 404);
        }
        return response()->json(['residence' => $residence]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'number' => [
                'required',
                'integer',
                Rule::unique('residences')->ignore($id),
            ],
            'name' => [
                'required',
                'string',
                'max:25',
                Rule::unique('residences')->ignore($id),
            ],
            'owner' => 'required',
            'active' => 'required',
        ]);

        DB::beginTransaction(); // Iniciar una transacción de base de datos
        try {
            // Buscar la residencia por el ID
            $residence = Residence::with('owner')->findOrFail($id);
            $before = clone $residence;  //capturar modelo antes de actualizar
            // Actualizar los campos con los nuevos valores
            $residence->number = $request->number;
            $residence->name = $request->name;
            $residence->active = $request->active;
            $residence->save();
            $residence->refresh();

            // Actualizar owner de la residencia
            $residence->owner()->sync($request->owner);
            $residence->load('owner');
            // Obtener names owners
            $owners = $residence->owner->pluck('name');
            $ownersArray = $owners->toArray();
            $limit = 3;
            if (count($ownersArray) > $limit) {
                $displayNames = array_slice($ownersArray, 0, $limit);
                $remainingCount = count($ownersArray) - $limit;
                $namesowners  = implode(' / ', $displayNames) . " y otros {$remainingCount} más...";
            } else {
                $namesowners  = implode(' / ', $ownersArray);
            }



            // Registrar la actividad
            $this->logActivity(
                'update',
                'Utilities',
                'Residencias',
                'Actualizo un registro',
                'residences',
                $before,
                $residence
            );

            DB::commit();
            return response()->json(['message' => 'Residencia actualizada con exito', 'residence' => $residence, 'owner' =>  $namesowners], 200);
        } catch (\Throwable $th) {
            DB::rollback(); // Revertir la transacción si hay algún error
            return response()->json(['error' => 'Problemas para actualizar esta residencia ' . $th->getMessage()], 500);
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
