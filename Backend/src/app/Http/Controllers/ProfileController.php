<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Usuario;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Contrato;

/**
 * Clase Controlador Perfil de un Usuario
 */
class ProfileController extends Controller
{
    /**
     * Completa los campos de cliente
     * 
     */
    public function add(Request $request) {
        // Obtener el usuario autenticado
        $user = Auth::user();

        // Validar los datos de entrada
        $validatedData = $request->validate([
            'apellidos' => 'required|string|max:255',
            'tlf' => 'required|unique:clientes,tlf|digits_between:9,15|regex:/^\+?\d+$/',
            'direccion' => 'required|string|max:255',
            'municipio' => 'required|string|max:255',
            'provincia' => 'required|string|max:255',
            'DNI' => 'required|string|size:9|unique:clientes,DNI|regex:/^\d{8}[A-Z]$/',
        ], [
            'apellidos.string' => 'Los apellidos deben ser una cadena de texto.',
            'apellidos.required' => 'Los apellidos son obligatorios.',

            'tlf.digits_between' => 'El número de teléfono tiene que ser una cadena entre 9 y 15 dígitos.' ,
            'tlf.required' => 'El número de teléfono es obligatorio',
            'tlf.regex' => 'El número de teléfono sólo puede tener números y opcionalmente +.',
            'tlf.unique' => 'Este número de teléfono ya esta en uso.',

            'direccion.string' => 'La direccion debe ser una cadena de texto.',
            'direccion.required' => 'La direccion es obligatorio.',

            'municipio.string' => 'El municipio debe ser una cadena de texto.',
            'municipio.required' => 'El municipio es obligatorio.',

            'provincia.string' => 'La provincia debe ser una cadena de texto.',
            'provincia.required' => 'La provincia es obligatorio.',

            'DNI.required' => 'El DNI es obligatorio.',
            'DNI.size' => 'El DNI debe tener 9 caracteres.',
            'DNI.regex' => 'El formato del DNI no es válido. Debe tener 8 números seguidos de una letra.',
            'DNI.unique' => 'El DNI ya está registrado en el sistema.',
        ]);

        // Crear el cliente y asociarlo con el usuario
        $cliente = Cliente::create([
            'usuario_id' => $user->id,  // Asociar el cliente al usuario autenticado
            'apellidos' => $validatedData['apellidos'],
            'tlf' => $validatedData['tlf'],
            'direccion' => $validatedData['direccion'],
            'municipio' => $validatedData['municipio'],
            'provincia' => $validatedData['provincia'],
            'DNI' => $validatedData['DNI'],
        ]);

        // 3) Crear contrato inicial
        if (! $cliente->contratos()->activos()->exists()) {
            $contrato = Contrato::create([
                'cliente_id'                      => $cliente->id,
                'numero_de_atenciones'            => 0,
                'numero_de_atenciones_realizadas' => 0,
                'fecha_inicio'                    => now()->toDateString(),
                'fecha_fin'                       => now()->addYear()->toDateString(),
                'estado'                          => 'activo',
            ]);
        }

        // Respuesta exitosa
        return response()->json([
            'message' => 'Cliente creado y asociado al usuario exitosamente',
            'usuario'  => [
                'id'            => $user->id,
                'nombre'        => $user->nombre,
                'nombreUsuario' => $user->nombreUsuario,
                'email'         => $user->email,
            ],
            'cliente'  => [
                'id'          => $cliente->id,
                'contrato_id' => $cliente->contrato_id,
                'apellidos'   => $cliente->apellidos,
                'tlf'         => $cliente->tlf,
                'direccion'   => $cliente->direccion,
                'municipio'   => $cliente->municipio,
                'provincia'   => $cliente->provincia,
                'DNI'         => $cliente->DNI,
            ],
            'contrato' => isset($contrato) ? [
                'id'                          => $contrato->id,
                'numero_de_atenciones'        => $contrato->numero_de_atenciones,
                'numero_de_atenciones_realizadas' => $contrato->numero_de_atenciones_realizadas,
                'fecha_inicio'                => $contrato->fecha_inicio,
                'fecha_fin'                   => $contrato->fecha_fin,
            ] : null,
        ], 201);
    }
    

    /**
     * Actualiza los campos
     * 
     */
    public function update(Request $request) {
        // Obtener el usuario autenticado
        $id = Auth::id();
        $user = Usuario::find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }

        // Validar los datos para el usuario
        $validatedUserData = $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
            'nombreUsuario' => 'sometimes|required|string|max:255|unique:usuarios,nombreUsuario,' . $user->id,
            'email' => 'sometimes|required|email|unique:usuarios,email,' . $user->id,
            'contrasena' => 'sometimes|string|min:8|confirmed',
        ], [
            'nombre.string' => 'El nombre tiene que ser una cadena de texto.',

            'nombreUsuario.unique' => 'El nombre de usuario ya está en uso.',
            'nombreUsuario.string' => 'El nombre de usuario tiene que ser una cadena de texto.',

            'email.email' => 'El email debe ser una dirección válida.',
            'email.unique' => 'El email ya está en uso.',
            
            'contrasena.min' => 'La contraseña tiene que tener al menos 8 caracteres.',
            'contrasena.confirmed' => 'Las contraseñas deben ser iguales'
        ]);

        // Validar los datos para el cliente
        $validatedClientData = $request->validate([
            'apellidos' => 'sometimes|required|string|max:255',
            'tlf' => 'sometimes|required|unique:clientes,tlf|digits_between:9,15|regex:/^\+?\d+$/',
            'direccion' => 'sometimes|required|string|max:255',
            'municipio' => 'sometimes|required|string|max:255',
            'provincia' => 'sometimes|required|string|max:255',
            'DNI' => 'sometimes|required|string|size:9|unique:clientes,DNI|regex:/^\d{8}[A-Z]$/',
        ], [
            'apellidos.string' => 'Los apellidos deben ser una cadena de texto.',

            'tlf.digits_between' => 'El número de teléfono tiene que ser una cadena de dígitos entre 9 y 15.' ,
            'tlf.regex' => 'El número de teléfono sólo puede tener números y opcionalmente +.',
            'tlf.unique' => 'Este número de teléfono ya esta en uso.',

            'direccion.string' => 'La direccion debe ser una cadena de texto.',

            'municipio.string' => 'El municipio debe ser una cadena de texto.',

            'provincia.string' => 'La provincia debe ser una cadena de texto.',

            'DNI.size' => 'El DNI debe tener 9 caracteres.',
            'DNI.regex' => 'El formato del DNI no es válido. Debe tener 8 números seguidos de una letra.',
            'DNI.unique' => 'El DNI ya está registrado en el sistema.',
        ]);

        $user->update($validatedUserData);

        // Actualizr o crear el cliente
        $cliente = Cliente::updateOrCreate(
        ['usuario_id' => $user->id],
        $validatedClientData
        );


        // Respuesta exitosa con los datos actualizados
        return response()->json([
            'message' => 'Datos de usuario y cliente actualizados exitosamente',
            'usuario' => $user,
            'cliente' => $cliente,
        ], 200);
    }

    /**
     * Actualiza solo contrasena
     * 
     */
    public function updatePwd(Request $request) {
        // Obtener el usuario autenticado
        $id = Auth::id();
        $user = Usuario::find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }

        // Validar los datos para el usuario
        $validatedUserData = $request->validate([
            'contrasena' => 'sometimes|string|min:8|confirmed',
        ], [
            'contrasena.min' => 'La contraseña tiene que tener al menos 8 caracteres.',
            'contrasena.confirmed' => 'Las contraseñas deben ser iguales'
        ]);

        $user->update($validatedUserData);


        if ($user->cliente) {
            $cliente = $user->cliente;
        } else {
            $cliente = [];
        }

        // Respuesta exitosa con los datos actualizados
        return response()->json([
            'message' => 'Datos de usuario y cliente actualizados exitosamente',
            'usuario' => $user,
            'cliente' => $cliente,
        ], 200);
    }

/**
 * Obtener datos de perfil (usuario + cliente).
 */
    public function show(Request $request)
    {
        $user = Auth::user();
        $clienteModel = $user->cliente;
        $contratoData = null;

        if ($clienteModel) {
            // Obtenemos el contrato a través de la relación hasOne()
            $contratoModel = $clienteModel->contrato;

            if ($contratoModel && $contratoModel->estado === 'activo') {
                $ahora = now();
                if ($ahora->greaterThan($contratoModel->fecha_fin)) {
                    $contratoModel->update(['estado' => 'finalizado']);
                } elseif ($contratoModel->numero_de_atenciones_realizadas >= $contratoModel->numero_de_atenciones) {
                    $contratoModel->update(['estado' => 'finalizado']);
                }
                $contratoModel->refresh();
            }

            $clienteData = [
                'id'          => $clienteModel->id,
                'apellidos'   => $clienteModel->apellidos,
                'tlf'         => $clienteModel->tlf,
                'direccion'   => $clienteModel->direccion,
                'municipio'   => $clienteModel->municipio,
                'provincia'   => $clienteModel->provincia,
                'DNI'         => $clienteModel->DNI,
            ];

            if (isset($contratoModel)) {
                $contratoData = [
                    'id'                              => $contratoModel->id,
                    'numero_de_atenciones'            => $contratoModel->numero_de_atenciones,
                    'numero_de_atenciones_realizadas' => $contratoModel->numero_de_atenciones_realizadas,
                    'fecha_inicio'                    => $contratoModel->fecha_inicio->toDateTimeString(),
                    'fecha_fin'                       => $contratoModel->fecha_fin->toDateTimeString(),
                    'estado'                          => $contratoModel->estado,
                ];
            }
        } else {
            $clienteData = [
                'id'          => null,
                'apellidos'   => '',
                'tlf'         => '',
                'direccion'   => '',
                'municipio'   => '',
                'provincia'   => '',
                'DNI'         => '',
            ];
        }

        return response()->json([
            'usuario'  => [
                'id'            => $user->id,
                'nombre'        => $user->nombre,
                'nombreUsuario' => $user->nombreUsuario,
                'email'         => $user->email,
            ],
            'cliente'  => $clienteData,
            'contrato' => $contratoData,
        ], 200);
    }
}