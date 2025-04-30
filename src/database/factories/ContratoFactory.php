<?php

namespace Database\Factories;

use App\Models\Cliente;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contrato>
 */
class ContratoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $cliente_id = Cliente::pluck('id')->toArray();
        $fecha_inicio = fake()->unique()->date();
        $fecha_fin = Carbon::parse($fecha_inicio)->addYear();
        return [
            'cliente_id' => fake()->randomElement($cliente_id),
            'numero_de_atenciones' => 10,
            'numero_de_atenciones_realizadas' => random_int(0,9),
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
        ];
    }
}
