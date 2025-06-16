<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Especialidad;

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
            'especialidad_id'  => Especialidad::inRandomOrder()->first()->id,
        ];
    }

}
