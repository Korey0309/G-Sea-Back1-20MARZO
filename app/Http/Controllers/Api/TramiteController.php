<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tramite;
use App\Models\User;
use Illuminate\Http\Request;

class TramiteController extends Controller
{
    public function index(Request $request)
    {
        $requestedWs = $request->filled('workspace_id') ? $request->integer('workspace_id') : null;
        $workspaceId = $this->resolveWorkspaceId($request, $requestedWs);

        return Tramite::query()
            ->where('workspace_id', $workspaceId)
            ->orderByDesc('fecha_ultima_modificacion')
            ->orderByDesc('id')
            ->paginate($request->integer('per_page', 15));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'workspace_id' => 'sometimes|exists:workspaces,id',
            'folio' => 'nullable|string|max:100',
            'tipo' => 'nullable|string|max:100',
            'tipo_tramite' => 'nullable|string|max:100',
            'etapa' => 'nullable|string|max:50',
            'nombre_agente' => 'nullable|string|max:255',
            'ramo' => 'nullable|string|max:100',
            'subramo' => 'nullable|string|max:100',
            'poliza_referencia' => 'nullable|string|max:100',
            'fecha_alta' => 'nullable|date',
            'observaciones' => 'nullable|string|max:555',
            'aseguradora' => 'nullable|string|max:555',
            'dias_fecha_alta' => 'nullable|string|max:555',
            'dias_etapa_actual' => 'nullable|string|max:555',
            'semaforo' => 'nullable|string|max:555',
            'centro_emisor' => 'nullable|string|max:100',
        ]);

        $workspaceId = $this->resolveWorkspaceId($request, $data['workspace_id'] ?? null);

        $tramite = Tramite::create(array_merge(
            collect($data)->except('workspace_id')->all(),
            [
                'workspace_id' => $workspaceId,
                'fecha_ultima_modificacion' => now(),
            ]
        ));

        return response()->json($tramite, 201);
    }

    public function show(Request $request, Tramite $tramite)
    {
        $this->abortIfOutOfWorkspace($request, $tramite->workspace_id);

        return $tramite->load('workspace');
    }

    public function update(Request $request, Tramite $tramite)
    {
        $activeWorkspaceId = $this->resolveWorkspaceId($request);
        $this->abortIfOutOfWorkspace($request, $tramite->workspace_id);

        $data = $request->validate([
            'workspace_id' => 'sometimes|required|exists:workspaces,id',
            'folio' => 'nullable|string|max:100',
            'tipo' => 'nullable|string|max:100',
            'tipo_tramite' => 'nullable|string|max:100',
            'etapa' => 'nullable|string|max:50',
            'nombre_agente' => 'nullable|string|max:255',
            'ramo' => 'nullable|string|max:100',
            'subramo' => 'nullable|string|max:100',
            'poliza_referencia' => 'nullable|string|max:100',
            'fecha_alta' => 'nullable|date',
            'observaciones' => 'nullable|string|max:555',
            'aseguradora' => 'nullable|string|max:555',
            'dias_fecha_alta' => 'nullable|string|max:555',
            'dias_etapa_actual' => 'nullable|string|max:555',
            'semaforo' => 'nullable|string|max:555',
            'centro_emisor' => 'nullable|string|max:100',
        ]);

        if (array_key_exists('workspace_id', $data) && (int) $data['workspace_id'] !== (int) $activeWorkspaceId) {
            return response()->json([
                'message' => 'El workspace enviado no coincide con el workspace activo.',
            ], 422);
        }

        $tramite->update(array_merge(
            collect($data)->except('workspace_id')->all(),
            ['fecha_ultima_modificacion' => now()]
        ));

        return $tramite->fresh();
    }

    public function destroy(Request $request, Tramite $tramite)
    {
        $this->abortIfOutOfWorkspace($request, $tramite->workspace_id);
        $tramite->delete();

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
