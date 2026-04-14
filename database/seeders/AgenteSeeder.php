<?php

namespace Database\Seeders;

use App\Models\Agente;
use Illuminate\Database\Seeder;

class AgenteSeeder extends Seeder
{
    public function run(): void
    {

        Agente::factory()->count(1500)->create();
    }
}
