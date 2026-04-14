<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ramo;
use Illuminate\Http\Request;

class RamoController extends Controller
{
    public function index(Request $request)
    {
        return Ramo::query()
            ->withCount('subramos')
            ->orderBy('nombre')
            ->paginate($request->integer('per_page', 15));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        $ramo = Ramo::create($data);

        return response()->json($ramo, 201);
    }

    public function show(Ramo $ramo)
    {
        return $ramo->load('subramos');
    }

    public function update(Request $request, Ramo $ramo)
    {
        $data = $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
        ]);

        $ramo->update($data);

        return $ramo->fresh();
    }

    public function destroy(Ramo $ramo)
    {
        $ramo->delete();

        return response()->noContent();
    }
}
