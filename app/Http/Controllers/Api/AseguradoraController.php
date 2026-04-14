<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Aseguradora;
use Illuminate\Http\Request;

class AseguradoraController extends Controller
{
    public function index(Request $request)
    {
        return Aseguradora::query()
            ->orderBy('nombre')
            ->paginate($request->integer('per_page', 15));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        $aseguradora = Aseguradora::create($data);

        return response()->json($aseguradora, 201);
    }

    public function show(Aseguradora $aseguradora)
    {
        return $aseguradora;
    }

    public function update(Request $request, Aseguradora $aseguradora)
    {
        $data = $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
        ]);

        $aseguradora->update($data);

        return $aseguradora->fresh();
    }

    public function destroy(Aseguradora $aseguradora)
    {
        $aseguradora->delete();

        return response()->noContent();
    }
}
