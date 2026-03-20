<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $table = 'planes';

    protected $fillable = [
        'nombre',
        'precio_mensual'
    ];

    public function workspaces()
    {
        return $this->hasMany(Workspace::class);
    }
}