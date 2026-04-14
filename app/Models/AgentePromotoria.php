<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentePromotoria extends Model
{
    protected $table = 'agentes_promotoria';

    protected $fillable = [
        'workspace_id',
        'user_id',
        'aseguradora_id',
        'clave_agente',
    ];

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function aseguradora()
    {
        return $this->belongsTo(Aseguradora::class);
    }

    public function polizas()
    {
        return $this->hasMany(Poliza::class, 'agente_id');
    }
}
