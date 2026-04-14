<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index(Request $request)
    {
        return Plan::query()
            ->orderBy('nombre')
            ->paginate($request->integer('per_page', 15));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'precio_mensual' => 'nullable|numeric|min:0',
        ]);

        $plan = Plan::create($data);

        return response()->json($plan, 201);
    }

    public function show(Plan $plan)
    {
        return $plan;
    }

    public function update(Request $request, Plan $plan)
    {
        $data = $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
            'precio_mensual' => 'nullable|numeric|min:0',
        ]);

        $plan->update($data);

        return $plan->fresh();
    }

    public function destroy(Plan $plan)
    {
        $plan->delete();

        return response()->noContent();
    }
}
