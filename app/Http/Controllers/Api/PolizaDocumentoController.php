<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PolizaDocumento;
use Illuminate\Http\Request;

class PolizaDocumentoController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'poliza_id' => 'sometimes|exists:polizas,id',
        ]);

        $query = PolizaDocumento::query()
            ->with('poliza')
            ->orderByDesc('id');

        if ($request->filled('poliza_id')) {
            $query->where('poliza_id', $request->integer('poliza_id'));
        }

        return $query->paginate($request->integer('per_page', 15));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'poliza_id' => 'required|exists:polizas,id',
            'tipo' => 'required|string|max:255',
            'ruta' => 'required|string|max:2048',
        ]);

        $doc = PolizaDocumento::create($data);

        return response()->json($doc->load('poliza'), 201);
    }

    public function show(PolizaDocumento $poliza_documento)
    {
        return $poliza_documento->load('poliza');
    }

    public function update(Request $request, PolizaDocumento $poliza_documento)
    {
        $data = $request->validate([
            'poliza_id' => 'sometimes|required|exists:polizas,id',
            'tipo' => 'sometimes|required|string|max:255',
            'ruta' => 'sometimes|required|string|max:2048',
        ]);

        $poliza_documento->update($data);

        return $poliza_documento->fresh()->load('poliza');
    }

    public function destroy(PolizaDocumento $poliza_documento)
    {
        $poliza_documento->delete();

        return response()->noContent();
    }
}
