<?php

namespace Database\Factories;

use App\Models\CitaServicio;
use App\Models\Cliente;
use App\Models\Contrato;
use App\Models\Empleado;
use App\Models\Servicio;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cita>
 */
class CitaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $clientes = Cliente::pluck('id')->toArray();
        $cliente_id = fake()->randomElement($clientes);
        $empleados = Empleado::pluck('id')->toArray();
        $empleado_id = fake()->randomElement($empleados);
       /*  $contratos = Contrato::pluck('id')->toArray();
        $contrato_id = fake()->randomElement($contratos);
        $contrato_id = Contrato::where('cliente_id', '=', strval(array_search($cliente_id, $contratos, true))); */
        /* $contrato_id = Contrato::find($cliente_id)->id; */ /* Al haber solo un contrato por cliente tienen el mismo id */
       /*  $contrato = Contrato::where('cliente_id', '=', $cliente_id)->get();
        $contrato_Id = Contrato::where('cliente_id', '=', $cliente_id)->value('id'); */

        return [
            'cliente_id' => $cliente_id,
            'empleado_id' => $empleado_id,
            'contrato_id' => $cliente_id,
            'fecha' => fake()->unique()->dateTime(),
            'estado' => fake()->randomElement(['pendiente', 'cancelado', 'completado']),
            'numero_de_atenciones' => 1,
        ];
    }
}
