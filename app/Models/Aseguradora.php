<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Aseguradora extends Model
{
    protected $table = 'aseguradoras';

    protected $fillable = [
        'nombre',
    ];

    public function agentes()
    {
        return $this->hasMany(AgenteWorkspaceAseguradora::class);
    }

    public function polizas()
    {
        return $this->hasMany(Poliza::class);
    }
}
