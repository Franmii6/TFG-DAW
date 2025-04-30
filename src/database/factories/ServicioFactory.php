<?php

namespace Database\Factories;

use App\Models\Cita;
use App\Models\Servicio;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Servicio>
 */
class ServicioFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->name(),
            'descripcion' => fake()->text(50),
            'duracion' => random_int(15, 120),
            'precio' => rand(1000, 10000) / 100,
        ];
    }

}
