<?php

namespace Database\Seeders;

use App\Models\Cita;
use App\Models\CitaServicio;
use App\Models\Cliente;
use App\Models\Contrato;
use App\Models\Empleado;
use App\Models\Especialidad;
use App\Models\Servicio;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    /*public function run(): void
    {
        // Usuarios con clientes y contratos
        Usuario::factory(10)->has(
            Cliente::factory()->has(
                Contrato::factory()
            )
        )->create();

        // Especialidades base
        Especialidad::factory()->create(['nombre' => 'cortar']);
        Especialidad::factory()->create(['nombre' => 'tintar']);
        Especialidad::factory()->create(['nombre' => 'barba']);

        // Usuarios que serán empleados
        Usuario::factory(3)->has(
            Empleado::factory()
        )->create();

        // Asignar aleatoriamente 1–3 especialidades a cada empleado
        Empleado::all()->each(function (Empleado $empleado) {
            $especialidadIds = Especialidad::inRandomOrder()
                                   ->take(2)
                                   ->pluck('id')
                                   ->toArray();
            $empleado->especialidades()->sync($especialidadIds);
        });

        // Servicios (cada uno enlazado a una especialidad por el factory)
        Servicio::factory(3)->create();

        // Citas y sus servicios
        //Cita::factory(100)->create();
        //CitaServicio::factory(random_int(200, 500))->create();
    }*/

    public function run(): void
    {

    // =================================================================
        // 1) CREAR EL USUARIO ADMINISTRADOR POR DEFECTO
        // =================================================================
        // Se crea un usuario específico para la administración del panel.
        // Este usuario no es un cliente ni un empleado, solo un administrador.
        Usuario::firstOrCreate(
            ['email' => 'admin@barberia.com'], // Busca por email para no duplicarlo
            [
                'nombre' => 'Administrador',
                'nombreUsuario' => 'Admin',
                // Usamos Hash::make para encriptar la contraseña. Si no, el login no funcionará.
                'contrasena' => Hash::make('password'),
            ]
        );

        // =================================================================
    // 1) Usuarios con clientes y contratos
        Usuario::factory(10)->has(
            Cliente::factory()->has(
                Contrato::factory()
            )
        )->create();

        // 2) Especialidades base: "cortar", "barba" y "cortar y barba"
        Especialidad::factory()->create(['nombre' => 'cortar']);          // id = 1
        Especialidad::factory()->create(['nombre' => 'barba']);           // id = 2
        Especialidad::factory()->create(['nombre' => 'cortar y barba']);  // id = 3

        // 3) Crear 3 usuarios que serán empleados, cada uno con su Empleado
        Usuario::factory(3)
            ->has(Empleado::factory())
            ->create();

        // 4) Asignar explícitamente especialidades:
        //    - Primer empleado → especialidad_id = 1 ("cortar")
        //    - Segundo empleado → especialidad_id = 2 ("barba")
        //    - Tercer empleado → especialidad_id = 3 ("cortar y barba")
        $empleados = Empleado::all()->take(3);

        if ($empleados->count() >= 3) {
            // Empleado 1 → solo "cortar" (id 1)
            $empleados[0]->especialidades()->sync([1]);

            // Empleado 2 → solo "barba" (id 2)
            $empleados[1]->especialidades()->sync([2]);

            // Empleado 3 → solo "cortar y barba" (id 3)
            $empleados[2]->especialidades()->sync([3]);
        }

        // 5) Servicios: crear uno para cada especialidad
        Servicio::factory()->create([
            'nombre'          => 'Servicio Corte',
            'descripcion'     => 'Corte de pelo básico',
            'duracion'        => 30,
            'precio'          => 15.00,
            'especialidad_id' => 1, // “cortar”
        ]);
        Servicio::factory()->create([
            'nombre'          => 'Servicio Barba',
            'descripcion'     => 'Arreglo de barba',
            'duracion'        => 20,
            'precio'          => 10.00,
            'especialidad_id' => 2, // “barba”
        ]);
        Servicio::factory()->create([
            'nombre'          => 'Servicio Corte & Barba',
            'descripcion'     => 'Corte de pelo y arreglo de barba',
            'duracion'        => 50,
            'precio'          => 25.00,
            'especialidad_id' => 3, // “cortar y barba”
        ]);

        // 6) Opcional: generar citas de prueba
        // Cita::factory(100)->create();
        // CitaServicio::factory(random_int(200, 500))->create();
    }
}
