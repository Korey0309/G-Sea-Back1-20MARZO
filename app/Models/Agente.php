<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agente extends Model
{
    use HasFactory;

    protected $fillable = [
        'cedula',
        'nombre',
        'apellido',
        'email',
        'telefono',
        'fecha_nacimiento',
        'curp',
        'rfc',
        'estado',
        'ciudad',
        'direccion',
        'fecha_alta',
        'activo',
        'foto_url',
    ];

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
            'fecha_alta' => 'date',
            'activo' => 'boolean',
        ];
    }

    public function workspaces()
    {
        return $this->hasMany(AgenteWorkspace::class);
    }
}
