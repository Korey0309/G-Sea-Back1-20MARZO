<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agente;
use App\Models\AgenteWorkspace;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AgentePromotoriaController extends Controller
{
    public function index(Request $request)
    {
        $workspaceId = $this->resolveWorkspaceId($request);

        $query = AgenteWorkspace::query()
            ->with(['agente', 'clavesAseguradora.aseguradora'])
            ->where('workspace_id', $workspaceId)
            ->orderByDesc('id');

        return $query->paginate($request->integer('per_page', 15));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'workspace_id' => 'sometimes|exists:workspaces,id',
            'status' => 'nullable|string|max:50',
            'agente' => 'required|array',
            'agente.cedula' => 'required|string|max:255',
            'agente.nombre' => 'required|string|max:255',
            'agente.apellido' => 'nullable|string|max:255',
            'agente.email' => 'nullable|email|max:255',
            'agente.telefono' => 'nullable|string|max:50',
            'agente.fecha_nacimiento' => 'nullable|date',
            'agente.curp' => 'nullable|string|max:18',
            'agente.rfc' => 'nullable|string|max:13',
            'agente.estado' => 'nullable|string|max:100',
            'agente.ciudad' => 'nullable|string|max:100',
            'agente.direccion' => 'nullable|string',
            'agente.fecha_alta' => 'nullable|date',
            'agente.activo' => 'nullable|boolean',
            'agente.foto_url' => 'nullable|string|max:2048',
            'claves' => 'nullable|array',
            'claves.*.aseguradora_id' => 'required_with:claves|exists:aseguradoras,id',
            'claves.*.clave_agente' => 'required_with:claves|string|max:255',
        ]);

        $reqWs = $data['workspace_id'] ?? null;
        if ($reqWs === 0 || $reqWs === '0') {
            $reqWs = null;
        }
        $workspaceId = $this->resolveWorkspaceId($request, $reqWs !== null ? (int) $reqWs : null);
        $agenteData = $data['agente'];

        return DB::transaction(function () use ($data, $agenteData, $workspaceId) {
            $agente = Agente::query()->firstOrNew([
                'cedula' => $agenteData['cedula'],
            ]);

            $agente->fill($agenteData);
            $agente->save();

            $agenteWorkspace = AgenteWorkspace::query()->firstOrCreate(
                [
                    'agente_id' => $agente->id,
                    'workspace_id' => $workspaceId,
                ],
                [
                    'status' => $data['status'] ?? 'activo',
                ]
            );

            if (! empty($data['status']) && $agenteWorkspace->status !== $data['status']) {
                $agenteWorkspace->status = $data['status'];
                $agenteWorkspace->save();
            }

            foreach ($data['claves'] ?? [] as $claveData) {
                $agenteWorkspace->clavesAseguradora()->updateOrCreate(
                    ['aseguradora_id' => $claveData['aseguradora_id']],
                    ['clave_agente' => $claveData['clave_agente']]
                );
            }

            return response()->json(
                $agenteWorkspace->fresh()->load(['agente', 'workspace', 'clavesAseguradora.aseguradora']),
                201
            );
        });
    }

    public function show(AgenteWorkspace $agentePromotoria)
    {
        $this->abortIfOutOfWorkspace(request(), $agentePromotoria->workspace_id);

        return $agentePromotoria->load(['agente', 'workspace', 'clavesAseguradora.aseguradora']);
    }

    public function update(Request $request, AgenteWorkspace $agentePromotoria)
    {
        $workspaceId = $this->resolveWorkspaceId($request);
        $this->abortIfOutOfWorkspace($request, $agentePromotoria->workspace_id);

        $data = $request->validate([
            'workspace_id' => [
                'sometimes',
                'required',
                'exists:workspaces,id',
                Rule::in([$workspaceId]),
            ],
            'status' => 'sometimes|required|string|max:50',
            'agente' => 'sometimes|required|array',
            'agente.cedula' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('agentes', 'cedula')->ignore($agentePromotoria->agente_id),
            ],
            'agente.nombre' => 'sometimes|required|string|max:255',
            'agente.apellido' => 'nullable|string|max:255',
            'agente.email' => 'nullable|email|max:255|unique:agentes,email,'.$agentePromotoria->agente_id,
            'agente.telefono' => 'nullable|string|max:50',
            'agente.fecha_nacimiento' => 'nullable|date',
            'agente.curp' => 'nullable|string|max:18',
            'agente.rfc' => 'nullable|string|max:13',
            'agente.estado' => 'nullable|string|max:100',
            'agente.ciudad' => 'nullable|string|max:100',
            'agente.direccion' => 'nullable|string',
            'agente.fecha_alta' => 'nullable|date',
            'agente.activo' => 'nullable|boolean',
            'agente.foto_url' => 'nullable|string|max:2048',
            'claves' => 'nullable|array',
            'claves.*.aseguradora_id' => 'required_with:claves|exists:aseguradoras,id',
            'claves.*.clave_agente' => 'required_with:claves|string|max:255',
        ]);

        return DB::transaction(function () use ($agentePromotoria, $data) {
            if (array_key_exists('status', $data)) {
                $agentePromotoria->status = $data['status'];
                $agentePromotoria->save();
            }

            if (! empty($data['agente'])) {
                $agentePromotoria->agente->update($data['agente']);
            }

            if (array_key_exists('claves', $data)) {
                $seenAseguradoras = [];

                foreach ($data['claves'] as $claveData) {
                    $seenAseguradoras[] = $claveData['aseguradora_id'];
                    $agentePromotoria->clavesAseguradora()->updateOrCreate(
                        ['aseguradora_id' => $claveData['aseguradora_id']],
                        ['clave_agente' => $claveData['clave_agente']]
                    );
                }

                $agentePromotoria->clavesAseguradora()
                    ->whereNotIn('aseguradora_id', $seenAseguradoras)
                    ->delete();
            }

            return $agentePromotoria->fresh()->load(['agente', 'workspace', 'clavesAseguradora.aseguradora']);
        });
    }

    public function destroy(AgenteWorkspace $agentePromotoria)
    {
        $this->abortIfOutOfWorkspace(request(), $agentePromotoria->workspace_id);
        $agentePromotoria->delete();

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
