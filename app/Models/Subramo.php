<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subramo extends Model
{
    protected $fillable = [
        'ramo_id',
        'nombre',
    ];

    public function ramo(): BelongsTo
    {
        return $this->belongsTo(Ramo::class);
    }

    public function polizas(): HasMany
    {
        return $this->hasMany(Poliza::class);
    }
}
