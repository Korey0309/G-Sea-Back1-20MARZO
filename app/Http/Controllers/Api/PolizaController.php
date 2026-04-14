<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgentePromotoria;
use App\Models\Contratante;
use App\Models\Poliza;
use App\Models\PolizaVehiculo;
use App\Models\Subramo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PolizaController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'workspace_id' => 'sometimes|exists:workspaces,id',
        ]);

        $query = Poliza::query()
            ->with([
                'contratante',
                'aseguradora',
                'ramo',
                'subramo',
                'agentePromotoria',
                'vehiculo',
            ])
            ->orderByDesc('inicio_vigencia');

        if ($request->filled('workspace_id')) {
            $query->where('workspace_id', $request->integer('workspace_id'));
        }

        if ($request->filled('contratante_id')) {
            $query->where('contratante_id', $request->integer('contratante_id'));
        }

        return $query->paginate($request->integer('per_page', 15));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'workspace_id' => 'required|exists:workspaces,id',
            'contratante_id' => 'required|exists:contratantes,id',
            'agente_id' => 'nullable|exists:agentes_promotoria,id',
            'aseguradora_id' => 'required|exists:aseguradoras,id',
            'ramo_id' => 'required|exists:ramos,id',
            'subramo_id' => 'required|exists:subramos,id',
            'numero_poliza' => 'required|string|max:255',
            'fecha_emision' => 'nullable|date',
            'inicio_vigencia' => 'required|date',
            'fin_vigencia' => 'required|date|after_or_equal:inicio_vigencia',
            'prima_neta' => 'nullable|numeric|min:0',
            'iva' => 'nullable|numeric|min:0',
            'prima_total' => 'nullable|numeric|min:0',
            'moneda' => 'nullable|string|max:10',
            'vehiculo' => 'nullable|array',
            'vehiculo.marca' => 'nullable|string|max:255',
            'vehiculo.modelo' => 'nullable|string|max:255',
            'vehiculo.anio' => 'nullable|integer|min:1900|max:2100',
            'vehiculo.serie' => 'nullable|string|max:255',
            'vehiculo.placas' => 'nullable|string|max:50',
            'vehiculo.motor' => 'nullable|string|max:255',
            'vehiculo.pasajeros' => 'nullable|integer|min:0',
        ]);

        $contratante = Contratante::findOrFail($data['contratante_id']);
        if ((int) $contratante->workspace_id !== (int) $data['workspace_id']) {
            return response()->json([
                'message' => 'El contratante no pertenece al workspace indicado.',
            ], 422);
        }

        if (! empty($data['agente_id'])) {
            $prom = AgentePromotoria::findOrFail($data['agente_id']);
            if ((int) $prom->workspace_id !== (int) $data['workspace_id']) {
                return response()->json([
                    'message' => 'El agente de promotoría no pertenece al workspace indicado.',
                ], 422);
            }
        }

        $subramo = Subramo::findOrFail($data['subramo_id']);
        if ((int) $subramo->ramo_id !== (int) $data['ramo_id']) {
            return response()->json([
                'message' => 'El subramo no corresponde al ramo indicado.',
            ], 422);
        }

        return DB::transaction(function () use ($data, $request) {
            $poliza = Poliza::create([
                'workspace_id' => $data['workspace_id'],
                'contratante_id' => $data['contratante_id'],
                'agente_id' => $data['agente_id'] ?? null,
                'aseguradora_id' => $data['aseguradora_id'],
                'ramo_id' => $data['ramo_id'],
                'subramo_id' => $data['subramo_id'],
                'numero_poliza' => $data['numero_poliza'],
                'fecha_emision' => $data['fecha_emision'] ?? null,
                'inicio_vigencia' => $data['inicio_vigencia'],
                'fin_vigencia' => $data['fin_vigencia'],
                'prima_neta' => $data['prima_neta'] ?? null,
                'iva' => $data['iva'] ?? null,
                'prima_total' => $data['prima_total'] ?? null,
                'moneda' => $data['moneda'] ?? 'MXN',
            ]);

            if ($request->filled('vehiculo') && is_array($request->input('vehiculo'))) {
                $vehAttrs = array_filter(
                    $request->input('vehiculo', []),
                    fn ($v) => $v !== null && $v !== ''
                );
                if ($vehAttrs !== []) {
                    PolizaVehiculo::create(array_merge(['poliza_id' => $poliza->id], $vehAttrs));
                }
            }

            return response()->json(
                $poliza->load(['contratante', 'aseguradora', 'ramo', 'subramo', 'agentePromotoria', 'vehiculo']),
                201
            );
        });
    }

    public function show(Poliza $poliza)
    {
        return $poliza->load([
            'workspace',
            'contratante',
            'aseguradora',
            'ramo',
            'subramo',
            'agentePromotoria',
            'vehiculo',
            'documentos',
        ]);
    }

    public function update(Request $request, Poliza $poliza)
    {
        $data = $request->validate([
            'workspace_id' => 'sometimes|required|exists:workspaces,id',
            'contratante_id' => 'sometimes|required|exists:contratantes,id',
            'agente_id' => 'nullable|exists:agentes_promotoria,id',
            'aseguradora_id' => 'sometimes|required|exists:aseguradoras,id',
            'ramo_id' => 'sometimes|required|exists:ramos,id',
            'subramo_id' => 'sometimes|required|exists:subramos,id',
            'numero_poliza' => 'sometimes|required|string|max:255',
            'fecha_emision' => 'nullable|date',
            'inicio_vigencia' => 'sometimes|required|date',
            'fin_vigencia' => 'sometimes|required|date',
            'prima_neta' => 'nullable|numeric|min:0',
            'iva' => 'nullable|numeric|min:0',
            'prima_total' => 'nullable|numeric|min:0',
            'moneda' => 'nullable|string|max:10',
            'vehiculo' => 'nullable|array',
            'vehiculo.marca' => 'nullable|string|max:255',
            'vehiculo.modelo' => 'nullable|string|max:255',
            'vehiculo.anio' => 'nullable|integer|min:1900|max:2100',
            'vehiculo.serie' => 'nullable|string|max:255',
            'vehiculo.placas' => 'nullable|string|max:50',
            'vehiculo.motor' => 'nullable|string|max:255',
            'vehiculo.pasajeros' => 'nullable|integer|min:0',
        ]);

        $workspaceId = $data['workspace_id'] ?? $poliza->workspace_id;
        $contratanteId = $data['contratante_id'] ?? $poliza->contratante_id;
        $ramoId = $data['ramo_id'] ?? $poliza->ramo_id;
        $subramoId = $data['subramo_id'] ?? $poliza->subramo_id;

        if (array_key_exists('contratante_id', $data)) {
            $c = Contratante::findOrFail($contratanteId);
            if ((int) $c->workspace_id !== (int) $workspaceId) {
                return response()->json([
                    'message' => 'El contratante no pertenece al workspace indicado.',
                ], 422);
            }
        }

        if (array_key_exists('agente_id', $data) && $data['agente_id'] !== null) {
            $prom = AgentePromotoria::findOrFail($data['agente_id']);
            if ((int) $prom->workspace_id !== (int) $workspaceId) {
                return response()->json([
                    'message' => 'El agente de promotoría no pertenece al workspace indicado.',
                ], 422);
            }
        }

        if (array_key_exists('subramo_id', $data) || array_key_exists('ramo_id', $data)) {
            $sub = Subramo::findOrFail($subramoId);
            if ((int) $sub->ramo_id !== (int) $ramoId) {
                return response()->json([
                    'message' => 'El subramo no corresponde al ramo indicado.',
                ], 422);
            }
        }

        return DB::transaction(function () use ($request, $poliza, $data) {
            $keys = [
                'workspace_id',
                'contratante_id',
                'aseguradora_id',
                'ramo_id',
                'subramo_id',
                'numero_poliza',
                'fecha_emision',
                'inicio_vigencia',
                'fin_vigencia',
                'prima_neta',
                'iva',
                'prima_total',
                'moneda',
            ];

            $updates = collect($data)->only($keys)->all();
            if (array_key_exists('agente_id', $data)) {
                $updates['agente_id'] = $data['agente_id'];
            }

            if ($updates !== []) {
                $poliza->update($updates);
            }

            if ($request->has('vehiculo')) {
                if ($request->input('vehiculo') === null) {
                    $poliza->vehiculo?->delete();
                } elseif (is_array($request->input('vehiculo'))) {
                    $raw = $request->input('vehiculo', []);
                    $attrs = array_filter($raw, fn ($v) => $v !== null && $v !== '');
                    PolizaVehiculo::updateOrCreate(
                        ['poliza_id' => $poliza->id],
                        $attrs
                    );
                }
            }

            return $poliza->fresh()->load([
                'contratante',
                'aseguradora',
                'ramo',
                'subramo',
                'agentePromotoria',
                'vehiculo',
            ]);
        });
    }

    public function destroy(Poliza $poliza)
    {
        $poliza->delete();

        return response()->noContent();
    }
}
