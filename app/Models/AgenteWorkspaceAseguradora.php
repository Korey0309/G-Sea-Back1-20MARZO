<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgenteWorkspaceAseguradora extends Model
{
    protected $table = 'agente_workspace_aseguradora';

    protected $fillable = [
        'agente_workspace_id',
        'aseguradora_id',
        'clave_agente',
    ];

    public function agenteWorkspace(): BelongsTo
    {
        return $this->belongsTo(AgenteWorkspace::class);
    }

    public function aseguradora(): BelongsTo
    {
        return $this->belongsTo(Aseguradora::class);
    }
}
