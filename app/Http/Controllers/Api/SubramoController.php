<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subramo;
use Illuminate\Http\Request;

class SubramoController extends Controller
{
    public function index(Request $request)
    {
        $query = Subramo::query()->with('ramo')->orderBy('nombre');

        if ($request->filled('ramo_id')) {
            $query->where('ramo_id', $request->integer('ramo_id'));
        }

        return $query->paginate($request->integer('per_page', 15));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ramo_id' => 'required|exists:ramos,id',
            'nombre' => 'required|string|max:255',
        ]);

        $subramo = Subramo::create($data);

        return response()->json($subramo->load('ramo'), 201);
    }

    public function show(Subramo $subramo)
    {
        return $subramo->load('ramo');
    }

    public function update(Request $request, Subramo $subramo)
    {
        $data = $request->validate([
            'ramo_id' => 'sometimes|required|exists:ramos,id',
            'nombre' => 'sometimes|required|string|max:255',
        ]);

        $subramo->update($data);

        return $subramo->fresh()->load('ramo');
    }

    public function destroy(Subramo $subramo)
    {
        $subramo->delete();

        return response()->noContent();
    }
}
