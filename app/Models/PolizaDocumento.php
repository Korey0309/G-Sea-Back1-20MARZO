<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PolizaDocumento extends Model
{
    protected $table = 'poliza_documentos';

    protected $fillable = [
        'poliza_id',
        'tipo',
        'ruta',
    ];

    public function poliza(): BelongsTo
    {
        return $this->belongsTo(Poliza::class);
    }
}
