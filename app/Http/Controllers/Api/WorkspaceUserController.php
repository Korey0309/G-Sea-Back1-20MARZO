<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkspaceUser;
use Illuminate\Http\Request;

class WorkspaceUserController extends Controller
{
    public function index(Request $request)
    {
        $query = WorkspaceUser::query()->with(['workspace', 'user', 'role'])->orderBy('id');

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
            'role_id' => 'required|exists:roles,id',
        ]);

        $row = WorkspaceUser::create($data);

        return response()->json($row->load(['workspace', 'user', 'role']), 201);
    }

    public function show(WorkspaceUser $workspace_user)
    {
        return $workspace_user->load(['workspace', 'user', 'role']);
    }

    public function update(Request $request, WorkspaceUser $workspace_user)
    {
        $data = $request->validate([
            'workspace_id' => 'sometimes|required|exists:workspaces,id',
            'user_id' => 'sometimes|required|exists:users,id',
            'role_id' => 'sometimes|required|exists:roles,id',
        ]);

        $workspace_user->update($data);

        return $workspace_user->fresh()->load(['workspace', 'user', 'role']);
    }

    public function destroy(WorkspaceUser $workspace_user)
    {
        $workspace_user->delete();

        return response()->noContent();
    }
}
