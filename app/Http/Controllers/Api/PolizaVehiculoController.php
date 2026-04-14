<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PolizaVehiculo;
use Illuminate\Http\Request;

class PolizaVehiculoController extends Controller
{
    public function index(Request $request)
    {
        $query = PolizaVehiculo::query()->with('poliza')->orderBy('id');

        if ($request->filled('poliza_id')) {
            $query->where('poliza_id', $request->integer('poliza_id'));
        }

        return $query->paginate($request->integer('per_page', 15));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'poliza_id' => 'required|exists:polizas,id',
            'marca' => 'nullable|string|max:255',
            'modelo' => 'nullable|string|max:255',
            'anio' => 'nullable|integer|min:1900|max:2100',
            'serie' => 'nullable|string|max:255',
            'placas' => 'nullable|string|max:50',
            'motor' => 'nullable|string|max:255',
            'pasajeros' => 'nullable|integer|min:0',
        ]);

        $row = PolizaVehiculo::create($data);

        return response()->json($row->load('poliza'), 201);
    }

    public function show(PolizaVehiculo $poliza_vehiculo)
    {
        return $poliza_vehiculo->load('poliza');
    }

    public function update(Request $request, PolizaVehiculo $poliza_vehiculo)
    {
        $data = $request->validate([
            'poliza_id' => 'sometimes|required|exists:polizas,id',
            'marca' => 'nullable|string|max:255',
            'modelo' => 'nullable|string|max:255',
            'anio' => 'nullable|integer|min:1900|max:2100',
            'serie' => 'nullable|string|max:255',
            'placas' => 'nullable|string|max:50',
            'motor' => 'nullable|string|max:255',
            'pasajeros' => 'nullable|integer|min:0',
        ]);

        $poliza_vehiculo->update($data);

        return $poliza_vehiculo->fresh()->load('poliza');
    }

    public function destroy(PolizaVehiculo $poliza_vehiculo)
    {
        $poliza_vehiculo->delete();

        return response()->noContent();
    }
}
