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
        $clientes = Cliente::pluck('id')->toArray(); // Guardo en una variable, un array con los id de los clientes
        $cliente_id = fake()->randomElement($clientes); // Selecciona un ID de cliente aleatorio del array `$clientes`
        $empleados = Empleado::pluck('id')->toArray(); // Guardo en una variable, un array con los id de los empleados
        $empleado_id = fake()->randomElement($empleados); // Selecciona un ID de empleado aleatorio del array `$empleados`
       /*  $contratos = Contrato::pluck('id')->toArray(); // Guardo en una variable, un array con los id de los contratos
        $contrato_id = fake()->randomElement($contratos); // Selecciona un ID de contrato aleatorio del array `$contratos` 
        $contrato_id = Contrato::where('cliente_id', '=', strval(array_search($cliente_id, $contratos, true))); */
        /* $contrato_id = Contrato::find($cliente_id)->id; */ /* Al haber solo un contrato por cliente tienen el mismo id */
       /*  $contrato = Contrato::where('cliente_id', '=', $cliente_id)->get(); 
        $contrato_Id = Contrato::where('cliente_id', '=', $cliente_id)->value('id'); */

        return [
            'cliente_id' => $cliente_id, // Asigna el ID del cliente aleatorio
            'empleado_id' => $empleado_id, // Asigna el ID del empleado aleatorio
            'contrato_id' => $cliente_id, // Asigna el ID del contrato (mismo que el cliente)
            'fecha' => fake()->unique()->dateTime(), // Asigna una fecha y hora aleatoria
            'estado' => fake()->randomElement(['pendiente', 'cancelado', 'completado']), // Asigna un estado aleatorio
            'numero_de_atenciones' => 0,
        ];
    }
}
