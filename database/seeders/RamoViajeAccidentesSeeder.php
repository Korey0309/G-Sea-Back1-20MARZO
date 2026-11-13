<?php

namespace Database\Seeders;

use App\Models\Ramo;
use App\Models\Subramo;
use Illuminate\Database\Seeder;

/** Agrega subramos Viajes / Accidentes personales a BD ya existentes. */
class RamoViajeAccidentesSeeder extends Seeder
{
    public function run(): void
    {
        $salud = Ramo::query()->where('nombre', 'ACCIDENTES Y ENFERMEDADES')->first();
        if (! $salud) {
            $salud = Ramo::query()->create(['nombre' => 'ACCIDENTES Y ENFERMEDADES']);
        }

        foreach (['Accidentes personales', 'Viajes'] as $nombre) {
            $exists = Subramo::query()
                ->where('ramo_id', $salud->id)
                ->where('nombre', $nombre)
                ->exists();

            if (! $exists) {
                Subramo::query()->create([
                    'ramo_id' => $salud->id,
                    'nombre' => $nombre,
                ]);
            }
        }
    }
}
