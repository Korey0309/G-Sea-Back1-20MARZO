<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contratante;
use App\Models\User;
use Illuminate\Http\Request;

class ContratanteController extends Controller
{
    /**
     * Listado del workspace activo del usuario (no confiar en workspace_id del cliente).
     */
    public function index(Request $request)
    {
        $requestedWs = $request->filled('workspace_id') ? $request->integer('workspace_id') : null;
        $workspaceId = $this->resolveWorkspaceId($request, $requestedWs);

        return Contratante::query()
            ->where('workspace_id', $workspaceId)
            ->withCount('polizas')
            ->orderBy('nombre')
            ->paginate($request->integer('per_page', 15));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'workspace_id' => 'sometimes|exists:workspaces,id',
            'nombre' => 'required|string|max:255',
            'rfc' => 'nullable|string|max:20',
            'telefono' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'direccion' => 'nullable|string',
        ]);

        $workspaceId = $this->resolveWorkspaceId($request, $data['workspace_id'] ?? null);

        $contratante = Contratante::create([
            'workspace_id' => $workspaceId,
            'nombre' => $data['nombre'],
            'rfc' => $data['rfc'] ?? null,
            'telefono' => $data['telefono'] ?? null,
            'email' => $data['email'] ?? null,
            'direccion' => $data['direccion'] ?? null,
        ]);

        return response()->json($contratante->loadCount('polizas'), 201);
    }

    public function show(Request $request, Contratante $contratante)
    {
        $this->abortIfOutOfWorkspace($request, $contratante->workspace_id);

        return $contratante->load(['workspace', 'polizas']);
    }

    public function update(Request $request, Contratante $contratante)
    {
        $activeWorkspaceId = $this->resolveWorkspaceId($request);
        $this->abortIfOutOfWorkspace($request, $contratante->workspace_id);

        $data = $request->validate([
            'workspace_id' => [
                'sometimes',
                'required',
                'exists:workspaces,id',
            ],
            'nombre' => 'sometimes|required|string|max:255',
            'rfc' => 'nullable|string|max:20',
            'telefono' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'direccion' => 'nullable|string',
        ]);

        if (array_key_exists('workspace_id', $data) && (int) $data['workspace_id'] !== (int) $activeWorkspaceId) {
            return response()->json([
                'message' => 'El workspace enviado no coincide con el workspace activo.',
            ], 422);
        }

        $contratante->update(collect($data)->except('workspace_id')->all());

        return $contratante->fresh()->loadCount('polizas');
    }

    public function destroy(Request $request, Contratante $contratante)
    {
        $this->abortIfOutOfWorkspace($request, $contratante->workspace_id);
        $contratante->delete();

        return response()->noContent();
    }

    private function resolveWorkspaceId(Request $request, ?int $requestedWorkspaceId = null): int
    {
        /** @var User $user */
        $user = $request->user();
        $activeWorkspaceId = $user->current_workspace_id;

        if (empty($activeWorkspaceId)) {
            abort(response()->json([
                'message' => 'No hay workspace activo para el usuario.',
            ], 422));
        }

        $hasAccess = $user->workspaces()
            ->where('workspaces.id', $activeWorkspaceId)
            ->exists();

        if (! $hasAccess) {
            abort(response()->json([
                'message' => 'El workspace activo no pertenece al usuario.',
            ], 403));
        }

        if ($requestedWorkspaceId === null || (int) $requestedWorkspaceId === 0) {
            $requestedWorkspaceId = null;
        }

        if ($requestedWorkspaceId !== null && (int) $requestedWorkspaceId !== (int) $activeWorkspaceId) {
            abort(response()->json([
                'message' => 'El workspace enviado no coincide con el workspace activo.',
            ], 422));
        }

        return (int) $activeWorkspaceId;
    }

    private function abortIfOutOfWorkspace(Request $request, int $resourceWorkspaceId): void
    {
        $workspaceId = $this->resolveWorkspaceId($request);

        if ((int) $workspaceId !== (int) $resourceWorkspaceId) {
            abort(response()->json([
                'message' => 'No tienes acceso a recursos de otro workspace.',
            ], 403));
        }
    }
}
