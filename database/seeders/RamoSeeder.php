<?php

namespace Database\Seeders;

use App\Models\Ramo;
use App\Models\Subramo;
use Illuminate\Database\Seeder;

class RamoSeeder extends Seeder
{
    public function run(): void
    {
        $vehiculos = Ramo::create(['nombre' => 'VEHÍCULOS']);
        $salud = Ramo::create(['nombre' => 'ACCIDENTES Y ENFERMEDADES']);
        $vida = Ramo::create(['nombre' => 'VIDA']);
        $danos = Ramo::create(['nombre' => 'DAÑOS']);

        Subramo::insert([
            ['ramo_id' => $vehiculos->id, 'nombre' => 'Automóviles', 'created_at' => now(), 'updated_at' => now()],
            ['ramo_id' => $vehiculos->id, 'nombre' => 'Motocicletas', 'created_at' => now(), 'updated_at' => now()],
            ['ramo_id' => $salud->id, 'nombre' => 'Gastos Médicos Mayores', 'created_at' => now(), 'updated_at' => now()],
            ['ramo_id' => $vida->id, 'nombre' => 'Vida', 'created_at' => now(), 'updated_at' => now()],
            ['ramo_id' => $danos->id, 'nombre' => 'Daños Empresariales', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
