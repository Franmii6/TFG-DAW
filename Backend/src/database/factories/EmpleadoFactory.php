<?php

namespace Database\Factories;

use App\Models\Empleado;
use App\Models\Especialidad;
use App\Models\Usuario;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Empleado>
 */
class EmpleadoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $usuario_id = Usuario::pluck('id')->toArray();

        return [
            'usuario_id' => fake()->unique()->randomElement($usuario_id),
            'DNI' => Str::random(9),
            'apellidos' => fake()->lastName(),
            'anos_experiencia' => random_int(0, 20),
            'tlf' => fake()->unique()->phoneNumber(),
            'direccion' => fake()->address(),
            'municipio' => fake()->city(),
            'provincia' => fake()->country(), // Country ya que no hay especifico para provincia 
        ];
    }
}
