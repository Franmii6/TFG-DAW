<?php

namespace Database\Factories;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cliente>
 */
class ClienteFactory extends Factory
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
            'usuario_id' => fake()->numberBetween(1, count($usuario_id)),
            'DNI' => Str::random(9),
            'apellidos' => fake()->lastName(),
            'tlf' => fake()->unique()->phoneNumber(),
            'direccion' => fake()->address(),
            'municipio' => fake()->city(),
            'provincia' => fake()->country(), // Country ya que no hay especifico para provincia 
        ];
    }
}
