<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Poliza extends Model
{
    protected $fillable = [
        'workspace_id',
        'contratante_id',
        'agente_id',
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
        'frecuencia_cobro',
        'monto_cuota',
        'telefono_notificacion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_emision' => 'date',
            'inicio_vigencia' => 'date',
            'fin_vigencia' => 'date',
            'prima_neta' => 'decimal:2',
            'iva' => 'decimal:2',
            'prima_total' => 'decimal:2',
            'monto_cuota' => 'decimal:2',
        ];
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function contratante(): BelongsTo
    {
        return $this->belongsTo(Contratante::class);
    }

    /** Asignacion del agente al workspace. */
    public function agenteWorkspace(): BelongsTo
    {
        return $this->belongsTo(AgenteWorkspace::class, 'agente_id');
    }

    public function aseguradora(): BelongsTo
    {
        return $this->belongsTo(Aseguradora::class);
    }

    public function ramo(): BelongsTo
    {
        return $this->belongsTo(Ramo::class);
    }

    public function subramo(): BelongsTo
    {
        return $this->belongsTo(Subramo::class);
    }

    public function vehiculo(): HasOne
    {
        return $this->hasOne(PolizaVehiculo::class);
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(PolizaDocumento::class);
    }

    public function cobranzaCuotas(): HasMany
    {
        return $this->hasMany(CobranzaCuota::class);
    }
}
