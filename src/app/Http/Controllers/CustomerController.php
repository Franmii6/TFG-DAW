<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

/**
 * Clase Controlador de Cliente y su usuario asociado
 */
class CustomerController extends Controller
{
    /**
     * Añade un cliente y un usuario
     * 
     * todo: le tengo que añadir tambien la contrasena
     */
    public function add(Request $request)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'nombreUsuario' => 'required|string|unique:usuarios,nombreUsuario|max:255',
            'apellidos' => 'required|string|max:255',
            'email' => 'required|email|unique:usuarios,email',
            'tlf' => 'required|unique:clientes,tlf|digits_between:9,15|regex:/^\+?\d+$/',
            'direccion' => 'required|string|max:255',
            'municipio' => 'required|string|max:255',
            'provincia' => 'required|string|max:255',
            'contrasena' => 'required|string|min:8',
            'DNI' => 'required|string|size:9|unique:clientes,DNI|regex:/^\d{8}[A-Z]$/',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.string' => 'El nombre tiene que ser una cadena de texto.',

            'nombreUsuario.required' => 'El nombre de usuario es obligatorio.',
            'nombreUsuario.string' => 'El nombre de usuario tiene que ser una cadena de texto.',
            'nombre.unique' => 'Este nombre de usuario ya está en uso.',

            'apellidos.required' => 'Los apellidos son obligatorios.',
            'apellidos.string' => 'Los apellidos tienen que ser una cadena de texto.',

            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email debe ser una dirección válida.',
            'email.unique' => 'El email ya está registrado.',

            'tlf.digits_between' => 'El número de teléfono tiene que ser una cadena entre 9 y 15 dígitos.',
            'tlf.required' => 'El número de teléfono es obligatorio',
            'tlf.regex' => 'El número de teléfono sólo puede tener números y opcionalmente +.',
            'tlf.unique' => 'Este número de teléfono ya esta en uso.',

            'direccion.string' => 'La direccion debe ser una cadena de texto.',
            'direccion.required' => 'La direccion es obligatorio.',

            'municipio.string' => 'El municipio debe ser una cadena de texto.',
            'municipio.required' => 'El municipio es obligatorio.',

            'provincia.string' => 'La provincia debe ser una cadena de texto.',
            'provincia.required' => 'La provincia es obligatorio.',

            'contrasena.required' => 'La contraseña es obligatoria.',
            'contrasena.min' => 'La contrasena tiene que estar compuesta por al menos 8 caracteres.',

            'DNI.required' => 'El DNI es obligatorio.',
            'DNI.size' => 'El DNI debe tener 9 caracteres.',
            'DNI.regex' => 'El formato del DNI no es válido. Debe tener 8 números seguidos de una letra.',
            'DNI.unique' => 'El DNI ya está registrado en el sistema.',
        ]);

        $usuario = Usuario::create([
            'nombre' => $validatedData['nombre'],
            'nombreUsuario' => $validatedData['nombreUsuario'],
            'email' => $validatedData['email'],
            'contrasena' => $validatedData['contrasena'],
        ]);

        $cliente = Cliente::create([
            'usuario_id' => $usuario->id,
            'apellidos' => $validatedData['apellidos'],
            'tlf' => $validatedData['tlf'],
            'direccion' => $validatedData['direccion'],
            'municipio' => $validatedData['municipio'],
            'provincia' => $validatedData['provincia'],
            'DNI' => $validatedData['DNI'],
        ]);

        return response()->json($cliente->load('usuario'), 200);
    }

    /**
     * Añade un cliente con un usuario ya creado
     */

    public function addClient(Request $request, $id)
    {
        $validatedData = $request->validate([
            'apellidos' => 'required|string|max:255',
            //'email' => 'required|email|unique:usuarios,email',
            'tlf' => 'required|unique:clientes,tlf|digits_between:9,15|regex:/^\+?\d+$/',
            'direccion' => 'required|string|max:255',
            'municipio' => 'required|string|max:255',
            'provincia' => 'required|string|max:255',
            'DNI' => 'required|string|size:9|unique:clientes,DNI|regex:/^\d{8}[A-Z]$/',
        ], [
            'apellidos.required' => 'Los apellidos son obligatorios.',
            'apellidos.string' => 'Los apellidos tienen que ser una cadena de texto.',

            //'email.required' => 'El email es obligatorio.',
            //'email.email' => 'El email debe ser una dirección válida.',
            //'email.unique' => 'El email ya está registrado.',

            'tlf.digits_between' => 'El número de teléfono tiene que ser una cadena entre 9 y 15 dígitos.',
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

        $cliente = Cliente::create([
            'usuario_id' => $id,
            'apellidos' => $validatedData['apellidos'],
            'tlf' => $validatedData['tlf'],
            'direccion' => $validatedData['direccion'],
            'municipio' => $validatedData['municipio'],
            'provincia' => $validatedData['provincia'],
            'DNI' => $validatedData['DNI'],
        ]);

        return response()->json($cliente->load('usuario'), 200);
    }

    /**
     * Muestra los clientes
     */
    public function show(Request $request)
    {

        $query = /* Cliente::with('usuario') */
            DB::table('clientes')
            ->select(
                'clientes.id',
                'clientes.apellidos',
                'clientes.tlf',
                'clientes.direccion',
                'clientes.municipio',
                'clientes.provincia',
                'clientes.DNI',
                'usuarios.email',
                'usuarios.contrasena',
                'usuarios.nombre',
                'usuarios.nombreUsuario'
            )
            ->join('usuarios', 'usuario_id', 'usuarios.id');

        if ($request->get('nombre')) {
            $query = $query->where('usuarios.nombre', 'LIKE', $request->get('nombre') . '%');
        }
        if ($request->get('apellidos')) {
            $query = $query->where('clientes.apellidos', 'LIKE', $request->get('apellidos') . '%');
        }
        if ($request->get('tlf')) {
            $query = $query->where('clientes.tlf', 'LIKE', '%' . $request->get('tlf') . '%');
        }
        if ($request->get('DNI')) {
            $query = $query->where('clientes.DNI', 'LIKE', '%' . $request->get('DNI') . '%');
        }

        $clientes = $query->get();

        if ($request->get('skip')) {
            $clientes = $clientes->skip((int)$request->get('skip'));
        }
        if ($request->get('take')) {
            $clientes = $clientes->take((int)$request->get('take'));
        } else {
            $clientes = $clientes->take(Cliente::count());
        }

        if ($clientes->isEmpty()) {
            return response()->json(['message' => 'No hay clientes en esta query'], 404);
        }

        return response()->json($clientes, 200);
    }

    /**
     * Obtiene un cliente
     */
    public function getCustomer($id)
    {
        $cliente = Cliente::with('usuario')->find($id);

        if (!$cliente) {
            return response()->json(['message' => 'Cliente no encontrado'], 404);
        }

        return response()->json($cliente, 200);
    }

    /**
     * Actualiza un cliente y su usuario asociado
     */
    public function update(Request $request, $id)
    {
        $cliente = Cliente::find($id);
        $usuario = Usuario::find($cliente->usuario_id);

        if (!$cliente) {
            return response()->json(['message' => 'Cliente no encontrado'], 404);
        }

        $validatedData = $request->validate([
            //'usuario_id' => 'sometimes|exists:usuarios,id',
            'DNI' => 'sometimes|string|size:9|unique:clientes,DNI|regex:/^\d{8}[A-Z]$/',
            'nombre' => 'sometimes|string|max:255',
            'nombreUsuario' => 'sometimes|unique:usuarios,nombreUsuario|string|max:255',
            'apellidos' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:usuarios,email,' . $cliente->usuario_id,
            'tlf' => 'sometimes|unique:clientes,tlf|digits_between:9,15|regex:/^\+?\d+$/',
            'direccion' => 'sometimes|string|max:255',
            'municipio' => 'sometimes|string|max:255',
            'provincia' => 'sometimes|string|max:255',
            'contrasena' => 'sometimes|string|min:8|confirmed', //necesita confirmar contraseña
        ], [
            'DNI.size' => 'El DNI debe tener 9 caracteres.',
            'DNI.regex' => 'El formato del DNI no es válido. Debe tener 8 números seguidos de una letra.',
            'DNI.unique' => 'El DNI ya está registrado en el sistema.',

            'nombre.string' => 'El nombre tiene que ser una cadena de texto.',

            'nombreUsuario.unique' => 'El nombre de usuario ya está en uso.',

            'apellidos.string' => 'Los apellidos deben ser una cadena de texto.',

            'email.email' => 'El email debe ser una dirección válida.',
            'email.unique' => 'El email ya está en uso.',

            'tlf.digits_between' => 'El número de teléfono tiene que ser una cadena de dígitos entre 9 y 15.',
            'tlf.regex' => 'El número de teléfono sólo puede tener números y opcionalmente +.',
            'tlf.unique' => 'Este número de teléfono ya esta en uso.',

            'direccion.string' => 'La direccion debe ser una cadena de texto.',

            'municipio.string' => 'El municipio debe ser una cadena de texto.',

            'provincia.string' => 'La provincia debe ser una cadena de texto.',

            'contrasena.min' => 'La contraseña tiene que tener al menos 8 caracteres.',
            'contrasena.confirmed' => 'Las contraseñas deben ser iguales'
        ]);

        // Verificar si 'DNI' está presente en los datos validados
        if (array_key_exists('DNI', $validatedData) && $validatedData['DNI'] == "") {
            $validatedData['DNI'] = $cliente->DNI;
        } elseif (!array_key_exists('DNI', $validatedData)) {
            $validatedData['DNI'] = $cliente->DNI;
        }

        $cliente->update($validatedData);
        $usuario->update($validatedData);

        return response()->json($cliente->load('usuario'), 200);
    }

    /**
     * Elimina un cliente y su usuario asociado
     */
    public function delete($id)
    {
        // Buscar el cliente por ID
        $cliente = Cliente::find($id);

        // Verificar si el cliente existe
        if (!$cliente) {
            return response()->json(['message' => 'Cliente no encontrado'], 404);
        }

        // Buscar el usuario asociado al cliente
        $usuario = Usuario::find($cliente->usuario_id);

        // Verificar si el usuario existe
        if (!$usuario) {
            return response()->json(['message' => 'Usuario asociado no encontrado'], 404);
        }

        // Intentar eliminar el cliente y su usuario asociado
        try {
            $cliente->delete();
            $usuario->delete();
            return response()->json(['message' => 'Cliente y usuario eliminados correctamente'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'No se pudo eliminar el cliente o el usuario', 'error' => $e->getMessage()], 500);
        }
    }
}
