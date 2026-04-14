<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PolizaVehiculo extends Model
{
    protected $table = 'poliza_vehiculos';

    protected $fillable = [
        'poliza_id',
        'marca',
        'modelo',
        'anio',
        'serie',
        'placas',
        'motor',
        'pasajeros',
    ];

    protected function casts(): array
    {
        return [
            'anio' => 'integer',
            'pasajeros' => 'integer',
        ];
    }

    public function poliza(): BelongsTo
    {
        return $this->belongsTo(Poliza::class);
    }
}
