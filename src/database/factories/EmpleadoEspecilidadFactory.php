<?php

namespace Database\Factories;

use App\Models\Empleado;
use App\Models\Especialidad;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EmpleadoEspecilidad>
 */
class EmpleadoEspecilidadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $empleado_id = Empleado::pluck('id')->toArray();
        $especialidad_id = Especialidad::pluck('id')->toArray();

        return [
            'empleado_id' => fake()->numberBetween(1, count($empleado_id))/* ->randomElement($empleado_id) */,
            'especialidad_id' => fake()->unique()->numberBetween(1, count($empleado_id)),
        ];
    }
}
