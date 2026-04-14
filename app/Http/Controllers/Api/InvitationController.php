<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InvitationController extends Controller
{
    public function index(Request $request)
    {
        $query = Invitation::query()->with(['workspace', 'role'])->orderByDesc('id');

        if ($request->filled('workspace_id')) {
            $query->where('workspace_id', $request->integer('workspace_id'));
        }

        return $query->paginate($request->integer('per_page', 15));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email|max:255',
            'workspace_id' => 'required|exists:workspaces,id',
            'role_id' => 'required|exists:roles,id',
            'expires_at' => 'nullable|date',
        ]);

        $data['token'] = Str::random(64);
        $data['used'] = false;

        $invitation = Invitation::create($data);

        return response()->json($invitation->load(['workspace', 'role']), 201);
    }

    public function show(Invitation $invitation)
    {
        return $invitation->load(['workspace', 'role']);
    }

    public function update(Request $request, Invitation $invitation)
    {
        $data = $request->validate([
            'email' => 'sometimes|required|email|max:255',
            'workspace_id' => 'sometimes|required|exists:workspaces,id',
            'role_id' => 'sometimes|required|exists:roles,id',
            'used' => 'boolean',
            'expires_at' => 'nullable|date',
        ]);

        $invitation->update($data);

        return $invitation->fresh()->load(['workspace', 'role']);
    }

    public function destroy(Invitation $invitation)
    {
        $invitation->delete();

        return response()->noContent();
    }
}
