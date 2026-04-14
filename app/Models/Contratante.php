<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contratante extends Model
{
    protected $fillable = [
        'workspace_id',
        'nombre',
        'rfc',
        'telefono',
        'email',
        'direccion',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function polizas(): HasMany
    {
        return $this->hasMany(Poliza::class);
    }
}
