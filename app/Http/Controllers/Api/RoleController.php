<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        return Role::query()
            ->orderBy('nombre')
            ->paginate($request->integer('per_page', 15));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        $role = Role::create($data);

        return response()->json($role, 201);
    }

    public function show(Role $role)
    {
        return $role;
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
        ]);

        $role->update($data);

        return $role->fresh();
    }

    public function destroy(Role $role)
    {
        $role->delete();

        return response()->noContent();
    }
}
