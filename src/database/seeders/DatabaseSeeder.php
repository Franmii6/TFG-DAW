<?php

namespace Database\Seeders;

use App\Models\Cita;
use App\Models\CitaServicio;
use App\Models\Cliente;
use App\Models\Contrato;
use App\Models\Empleado;
use App\Models\EmpleadoEspecilidad;
use App\Models\Especialidad;
use App\Models\Servicio;
use App\Models\Usuario;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Usuario::factory(10)->has(
            Cliente::factory()->has(
                Contrato::factory()
            )
        )->create();
        Especialidad::factory()->create(['nombre' => 'cortar']);
        Especialidad::factory()->create(['nombre' => 'tintar']);
        Especialidad::factory()->create(['nombre' => 'degradar']);
        Usuario::factory(3)->has(
            Empleado::factory()
        )->create();
        EmpleadoEspecilidad::factory(3)->create();
        Servicio::factory(3)->create();
        Cita::factory(100)->create();
        CitaServicio::factory(random_int(200, 500))->create();
    }
}
