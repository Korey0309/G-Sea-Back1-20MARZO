<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgentePromotoria;
use Illuminate\Http\Request;

class AgentePromotoriaController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'workspace_id' => 'sometimes|exists:workspaces,id',
        ]);

        $query = AgentePromotoria::query()
            ->with(['aseguradora', 'user'])
            ->orderBy('clave_agente');

        if ($request->filled('workspace_id')) {
            $query->where('workspace_id', $request->integer('workspace_id'));
        }

        return $query->paginate($request->integer('per_page', 15));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'workspace_id' => 'required|exists:workspaces,id',
            'user_id' => 'required|exists:users,id',
            'aseguradora_id' => 'required|exists:aseguradoras,id',
            'clave_agente' => 'required|string|max:255',
        ]);

        $row = AgentePromotoria::create($data);

        return response()->json($row->load(['aseguradora', 'user']), 201);
    }

    public function show(AgentePromotoria $agentePromotoria)
    {
        return $agentePromotoria->load(['workspace', 'aseguradora', 'user', 'polizas']);
    }

    public function update(Request $request, AgentePromotoria $agentePromotoria)
    {
        $data = $request->validate([
            'workspace_id' => 'sometimes|required|exists:workspaces,id',
            'user_id' => 'sometimes|required|exists:users,id',
            'aseguradora_id' => 'sometimes|required|exists:aseguradoras,id',
            'clave_agente' => 'sometimes|required|string|max:255',
        ]);

        $agentePromotoria->update($data);

        return $agentePromotoria->fresh()->load(['aseguradora', 'user']);
    }

    public function destroy(AgentePromotoria $agentePromotoria)
    {
        $agentePromotoria->delete();

        return response()->noContent();
    }
}
