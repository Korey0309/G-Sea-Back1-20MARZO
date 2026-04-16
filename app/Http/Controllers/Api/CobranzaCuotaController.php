<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CobranzaCuota;
use App\Models\Poliza;
use App\Models\User;
use Illuminate\Http\Request;

class CobranzaCuotaController extends Controller
{
    public function index(Request $request)
    {
        $requestedWs = $request->filled('workspace_id') ? $request->integer('workspace_id') : null;
        $workspaceId = $this->resolveWorkspaceId($request, $requestedWs);

        $query = CobranzaCuota::query()
            ->with(['poliza.contratante', 'poliza.aseguradora', 'poliza.agenteWorkspace.agente'])
            ->where('workspace_id', $workspaceId)
            ->orderBy('fecha_programada')
            ->orderBy('numero_cuota');

        if ($request->filled('poliza_id')) {
            $query->where('poliza_id', $request->integer('poliza_id'));
        }

        if ($request->filled('estatus')) {
            $query->where('estatus', $request->string('estatus'));
        }

        if ($request->filled('desde')) {
            $query->whereDate('fecha_programada', '>=', $request->date('desde')->toDateString());
        }

        if ($request->filled('hasta')) {
            $query->whereDate('fecha_programada', '<=', $request->date('hasta')->toDateString());
        }

        return $query->paginate($request->integer('per_page', 30));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'workspace_id' => 'sometimes|exists:workspaces,id',
            'poliza_id' => 'required|exists:polizas,id',
            'numero_cuota' => 'nullable|integer|min:1',
            'fecha_programada' => 'required|date',
            'monto' => 'required|numeric|min:0',
            'estatus' => 'nullable|in:pendiente,pagado,vencido,cancelado',
            'telefono_notificacion' => 'nullable|string|max:30',
        ]);

        $workspaceId = $this->resolveWorkspaceId($request, $data['workspace_id'] ?? null);

        $poliza = Poliza::findOrFail($data['poliza_id']);
        if ((int) $poliza->workspace_id !== (int) $workspaceId) {
            return response()->json([
                'message' => 'La póliza no pertenece al workspace activo.',
            ], 422);
        }

        $numero = $data['numero_cuota'] ?? null;
        if ($numero === null) {
            $numero = (int) CobranzaCuota::query()
                ->where('poliza_id', $poliza->id)
                ->max('numero_cuota') + 1;
        }

        $cuota = CobranzaCuota::create([
            'workspace_id' => $workspaceId,
            'poliza_id' => $poliza->id,
            'numero_cuota' => $numero,
            'fecha_programada' => $data['fecha_programada'],
            'monto' => $data['monto'],
            'estatus' => $data['estatus'] ?? 'pendiente',
            'telefono_notificacion' => $data['telefono_notificacion'] ?? null,
        ]);

        return response()->json($cuota->load(['poliza.contratante']), 201);
    }

    public function show(Request $request, CobranzaCuota $cobranzaCuota)
    {
        $this->abortIfOutOfWorkspace($request, $cobranzaCuota->workspace_id);

        return $cobranzaCuota->load(['poliza.contratante', 'poliza.aseguradora']);
    }

    public function update(Request $request, CobranzaCuota $cobranzaCuota)
    {
        $this->abortIfOutOfWorkspace($request, $cobranzaCuota->workspace_id);

        $data = $request->validate([
            'fecha_programada' => 'sometimes|date',
            'monto' => 'sometimes|numeric|min:0',
            'estatus' => 'sometimes|in:pendiente,pagado,vencido,cancelado',
            'telefono_notificacion' => 'nullable|string|max:30',
        ]);

        $cobranzaCuota->update($data);

        return $cobranzaCuota->fresh()->load(['poliza.contratante']);
    }

    public function destroy(Request $request, CobranzaCuota $cobranzaCuota)
    {
        $this->abortIfOutOfWorkspace($request, $cobranzaCuota->workspace_id);
        $cobranzaCuota->delete();

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
