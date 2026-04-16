<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Exceptions\HttpResponseException;

class CobranzaCuota extends Model
{
    protected $table = 'cobranza_cuotas';

    protected $fillable = [
        'workspace_id',
        'poliza_id',
        'numero_cuota',
        'fecha_programada',
        'monto',
        'estatus',
        'telefono_notificacion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_programada' => 'date',
            'monto' => 'decimal:2',
        ];
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function poliza(): BelongsTo
    {
        return $this->belongsTo(Poliza::class);
    }

    /**
     * Resolución por ruta acotada al workspace activo (evita 404 crípticos y filtra por tenant).
     *
     * @param  mixed  $value
     * @param  string|null  $field
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $field ??= $this->getRouteKeyName();
        $user = request()->user();

        if (! $user || empty($user->current_workspace_id)) {
            throw new HttpResponseException(response()->json([
                'message' => 'No hay workspace activo para el usuario.',
            ], 422));
        }

        $workspaceId = (int) $user->current_workspace_id;

        $cuota = static::query()
            ->where($field, $value)
            ->where('workspace_id', $workspaceId)
            ->first();

        if ($cuota) {
            return $cuota;
        }

        if (static::query()->where($field, $value)->exists()) {
            throw new HttpResponseException(response()->json([
                'message' => 'Esta cuota no pertenece al workspace activo.',
            ], 403));
        }

        throw new HttpResponseException(response()->json([
            'message' => 'La cuota de cobranza no existe o fue eliminada. Vuelve a cargar la lista (puede haber cambiado el id tras migrar o regenerar cuotas).',
        ], 404));
    }
}
