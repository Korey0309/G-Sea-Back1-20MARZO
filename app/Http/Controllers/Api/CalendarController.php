<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CobranzaCuota;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    /**
     * Cuotas programadas en un rango (calendario / seguimiento de cobranza).
     * Query: from, to (Y-m-d) o year + month (1-12).
     */
    public function cobranzaCuotas(Request $request)
    {
        $requestedWs = $request->filled('workspace_id') ? $request->integer('workspace_id') : null;
        $workspaceId = $this->resolveWorkspaceId($request, $requestedWs);

        if ($request->filled('year') && $request->filled('month')) {
            $start = Carbon::createFromDate(
                $request->integer('year'),
                $request->integer('month'),
                1
            )->startOfDay();
            $end = $start->copy()->endOfMonth();
        } else {
            $start = $request->date('from')?->startOfDay() ?? now()->startOfMonth();
            $end = $request->date('to')?->endOfDay() ?? now()->endOfMonth();
        }

        $query = CobranzaCuota::query()
            ->with(['poliza.contratante', 'poliza.aseguradora', 'poliza.agenteWorkspace.agente'])
            ->where('workspace_id', $workspaceId)
            ->whereBetween('fecha_programada', [$start->toDateString(), $end->toDateString()])
            ->orderBy('fecha_programada')
            ->orderBy('numero_cuota');

        if ($request->filled('estatus')) {
            $query->where('estatus', $request->string('estatus'));
        }

        return $query->get();
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
}
