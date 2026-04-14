<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ramo extends Model
{
    protected $fillable = [
        'nombre',
    ];

    public function subramos(): HasMany
    {
        return $this->hasMany(Subramo::class);
    }

    public function polizas(): HasMany
    {
        return $this->hasMany(Poliza::class);
    }
}
