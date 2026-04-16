<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AgenteWorkspace extends Model
{
    protected $fillable = [
        'agente_id',
        'workspace_id',
        'status',
    ];

    public function agente(): BelongsTo
    {
        return $this->belongsTo(Agente::class);
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function clavesAseguradora(): HasMany
    {
        return $this->hasMany(AgenteWorkspaceAseguradora::class);
    }
}
