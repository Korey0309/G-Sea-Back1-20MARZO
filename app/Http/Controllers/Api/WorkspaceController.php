<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WorkspaceController extends Controller
{
    public function index(Request $request)
    {
        return Workspace::query()
            ->with(['plan', 'owner'])
            ->orderBy('nombre')
            ->paginate($request->integer('per_page', 15));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:workspaces,slug',
            'plan_id' => 'nullable|exists:planes,id',
            'owner_id' => 'required|exists:users,id',
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['nombre']).'-'.Str::lower(Str::random(6));
        }

        $workspace = Workspace::create($data);

        return response()->json($workspace->load(['plan', 'owner']), 201);
    }

    public function show(Workspace $workspace)
    {
        return $workspace->load(['plan', 'owner', 'users']);
    }

    public function update(Request $request, Workspace $workspace)
    {
        $data = $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|max:255|unique:workspaces,slug,'.$workspace->id,
            'plan_id' => 'nullable|exists:planes,id',
            'owner_id' => 'sometimes|required|exists:users,id',
        ]);

        $workspace->update($data);

        return $workspace->fresh()->load(['plan', 'owner']);
    }

    public function destroy(Workspace $workspace)
    {
        $workspace->delete();

        return response()->noContent();
    }
}
