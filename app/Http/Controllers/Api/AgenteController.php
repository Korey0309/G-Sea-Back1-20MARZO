<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agente;
use Illuminate\Http\Request;

class AgenteController extends Controller
{
    public function index(Request $request)
    {
        return Agente::query()
            ->orderBy('nombre')
            ->paginate($request->integer('per_page', 15));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255|unique:agentes,email',
            'telefono' => 'nullable|string|max:50',
            'fecha_nacimiento' => 'nullable|date',
            'curp' => 'nullable|string|max:18',
            'rfc' => 'nullable|string|max:13',
            'estado' => 'nullable|string|max:100',
            'ciudad' => 'nullable|string|max:100',
            'direccion' => 'nullable|string',
            'fecha_alta' => 'nullable|date',
            'activo' => 'boolean',
        ]);

        $agente = Agente::create($data);

        return response()->json($agente, 201);
    }

    public function show(Agente $agente)
    {
        return $agente;
    }

    public function update(Request $request, Agente $agente)
    {
        $data = $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
            'apellido' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255|unique:agentes,email,'.$agente->id,
            'telefono' => 'nullable|string|max:50',
            'fecha_nacimiento' => 'nullable|date',
            'curp' => 'nullable|string|max:18',
            'rfc' => 'nullable|string|max:13',
            'estado' => 'nullable|string|max:100',
            'ciudad' => 'nullable|string|max:100',
            'direccion' => 'nullable|string',
            'fecha_alta' => 'nullable|date',
            'activo' => 'boolean',
        ]);

        $agente->update($data);

        return $agente->fresh();
    }

    public function destroy(Agente $agente)
    {
        $agente->delete();

        return response()->noContent();
    }
}
