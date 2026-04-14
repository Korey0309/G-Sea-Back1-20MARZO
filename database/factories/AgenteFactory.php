<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AgenteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre' => $this->faker->firstName,
            'apellido' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'telefono' => $this->faker->phoneNumber,
            'fecha_nacimiento' => $this->faker->date(),
            'curp' => strtoupper($this->faker->bothify('????######????')),
            'rfc' => strtoupper($this->faker->bothify('????######')),
            'estado' => 'Yucatán',
            'ciudad' => 'Mérida',
            'direccion' => $this->faker->address,
            'fecha_alta' => now(),
            'activo' => 1,
        ];
    }
}
