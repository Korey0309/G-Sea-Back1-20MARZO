<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contratante;
use Illuminate\Http\Request;

class ContratanteController extends Controller
{
    /**
     * Listado por workspace. El contratante es la base: de él dependen las pólizas.
     */
    public function index(Request $request)
    {
        $request->validate([
            'workspace_id' => 'sometimes|exists:workspaces,id',
        ]);

        $query = Contratante::query()
            ->withCount('polizas')
            ->orderBy('nombre');

        if ($request->filled('workspace_id')) {
            $query->where('workspace_id', $request->integer('workspace_id'));
        }

        return $query->paginate($request->integer('per_page', 15));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'workspace_id' => 'required|exists:workspaces,id',
            'nombre' => 'required|string|max:255',
            'rfc' => 'nullable|string|max:20',
            'telefono' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'direccion' => 'nullable|string',
        ]);

        $contratante = Contratante::create($data);

        return response()->json($contratante->loadCount('polizas'), 201);
    }

    public function show(Contratante $contratante)
    {
        return $contratante->load(['workspace', 'polizas']);
    }

    public function update(Request $request, Contratante $contratante)
    {
        $data = $request->validate([
            'workspace_id' => 'sometimes|required|exists:workspaces,id',
            'nombre' => 'sometimes|required|string|max:255',
            'rfc' => 'nullable|string|max:20',
            'telefono' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'direccion' => 'nullable|string',
        ]);

        $contratante->update($data);

        return $contratante->fresh()->loadCount('polizas');
    }

    public function destroy(Contratante $contratante)
    {
        $contratante->delete();

        return response()->noContent();
    }
}
