<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empleado;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Clase Controlador de Empleados
 */
class EmployeeController extends Controller
{
    /**
     * Añade un empleado
     */
    public function add(Request $request) {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'nombreUsuario' => 'required|string|max:255|unique:usuarios,nombreUsuario',
            'apellidos' => 'required|string|max:255',
            'email' => 'required|email|unique:usuarios,email',
            'tlf' => 'required|unique:empleados,tlf|digits_between:9,15|regex:/^\+?\d+$/',
            'direccion' => 'required|string|max:255',
            'municipio' => 'required|string|max:255',
            'provincia' => 'required|string|max:255',
            'contrasena' => 'required|string|min:8',
            'DNI' => 'required|string|size:9|unique:empleados,DNI|regex:/^\d{8}[A-Z]$/',
            'anos_experiencia' => 'required|integer|max:80'
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.string' => 'El nombre debe ser una cadena de texto.',
            
            'nombreUsuario.required' => 'El nombre de usuario es obligatorio.',
            'nombreUsuario.unique' => 'Este nombre de usuario ya está en uso.',
            'nombreUsuario.string' => 'El nombre de usuario debe ser una cadena de texto.',

            'apellidos.required' => 'Los apellidos son obligatorios.',
            'apellidos.string' => 'Los apellidos deben ser una cadena de texto.',

            'email.email' => 'El email debe ser un email válido',
            'email.required' => 'El email es obligatorio.',
            'email.unique' => 'Este email ya está en uso.',

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

            'contrasena.required' => 'La contraseña es obligatoria.',
            'contrasena.min' => 'La contrasena tiene que estar compuesta por al menos 8 caracteres.',

            'DNI.required' => 'El DNI es obligatorio.',
            'DNI.size' => 'El DNI debe tener 9 caracteres.',
            'DNI.regex' => 'El formato del DNI no es válido. Debe tener 8 números seguidos de una letra.',
            'DNI.unique' => 'El DNI ya está registrado en el sistema.',

            'anos_experiencia.required' => 'Los años de experiencia son obligatorios.',
            'anos_experiencia.integer' => 'Los años de experiencia deben ser un número entero.',
            'anos_experiencia.max' => 'Los años de experiencia no pueden ser superior a 80.',
        ]);

        $usuario = Usuario::create([
            'nombre' => $validatedData['nombre'],
            'nombreUsuario' => $validatedData['nombreUsuario'],
            'email' => $validatedData['email'],
            'contrasena' => $validatedData['contrasena'],
        ]);

        $empleado = Empleado::create([
            'usuario_id' => $usuario->id,
            'apellidos' => $validatedData['apellidos'],
            'tlf' => $validatedData['tlf'],
            'direccion' => $validatedData['direccion'],
            'municipio' => $validatedData['municipio'],
            'provincia' => $validatedData['provincia'],
            'anos_experiencia' => $validatedData['anos_experiencia'],
            'DNI' => $validatedData['DNI'],
        ]);

        return response()->json($empleado->load('usuario'), 200);
    }

    /**
     * Creamos un empleado y lo asociamos a un usuario que ya existe en el sistema
     */
    public function addEmployee(Request $request, $id){

        try{
            $usuario = Usuario::findOrFail($id);
        }
        catch (ModelNotFoundException){
            return response()->json(['message' => 'usuario no encontrado'], 404);
        }


        $validatedData = $request->validate([
            'apellidos' => 'required|string|max:255',
            'tlf' => 'required|unique:empleados,tlf|digits_between:9,15|regex:/^\+?\d+$/',
            'direccion' => 'required|string|max:255',
            'municipio' => 'required|string|max:255',
            'provincia' => 'required|string|max:255',
            'DNI' => 'required|string|size:9|unique:empleados,DNI|regex:/^\d{8}[A-Z]$/',
            'anos_experiencia' => 'required|integer|max:80'
        ], [
            'apellidos.required' => 'Los apellidos son obligatorios.',
            'apellidos.string' => 'Los apellidos deben ser una cadena de texto.',

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

            'anos_experiencia.required' => 'Los años de experiencia son obligatorios.',
            'anos_experiencia.integer' => 'Los años de experiencia deben ser un número entero.',
            'anos_experiencia.max' => 'Los años de experiencia no pueden ser superior a 80.',
        ]);

        $empleado = Empleado::create([
            'usuario_id' => $id,
            'apellidos' => $validatedData['apellidos'],
            'tlf' => $validatedData['tlf'],
            'direccion' => $validatedData['direccion'],
            'municipio' => $validatedData['municipio'],
            'provincia' => $validatedData['provincia'],
            'anos_experiencia' => $validatedData['anos_experiencia'],
            'DNI' => $validatedData['DNI'],
        ]);

        return response()->json($empleado->load('usuario'), 200);
    }

    /**
     * Muestra los empelados
     */
    public function show(Request $request) {

        $query = Empleado::with('usuario')
            ->select('empleados.*')
            ->join('usuarios', 'usuario_id', 'usuarios.id');

        if ($request->get('nombre')) {
            $query = $query->where('usuarios.nombre', 'LIKE', $request->get('nombre') . '%');
        }
        if ($request->get('apellidos')) {
            $query = $query->where('empleados.apellidos', 'LIKE', $request->get('apellidos') . '%');
        }
        if ($request->get('tlf')) {
            $query = $query->where('empleados.tlf', 'LIKE', '%' . $request->get('tlf') . '%');
        }
        if ($request->get('DNI')) {
            $query = $query->where('empleados.DNI', 'LIKE', '%' . $request->get('DNI') . '%');
        }

        $empleados = $query->get();

        if ($request->get('skip')) {
            $empleados = $empleados->skip((int)$request->get('skip'));
        }
        if ($request->get('take')) {
            $empleados = $empleados->take((int)$request->get('take'));
        } else {
            $empleados = $empleados->take(Empleado::count());
        }

        if ($empleados->isEmpty()) {
            return response()->json(['message' => 'No hay empleados en esta query'], 404);
        }

        return response()->json($empleados, 200);
    }

    /**
     * Obtiene un empleado
     */
    public function getEmployee(Request $request, $id) {
        $empleado = Empleado::with('usuario')->find($id);

        if (!$empleado) {
            return response()->json(['message' => 'Empleado no encontrado, no esta registrado en el sistema'], 404);
        }

        return response()->json($empleado->load('usuario'), 200);
    }

    /**
     * Modifica un empleado
     */
    public function update(Request $request, $id) {
        $empleado = Empleado::find($id);
        $usuario = Usuario::find($empleado->usuario_id);

        if (!$empleado) {
            return response()->json(['message' => 'empleado no encontrado'], 404);
        }

        $validatedData = $request->validate([
            'usuario_id' => 'sometimes|exists:usuarios,id',
            'DNI' => 'sometimes|string|size:9|unique:empleados,DNI|regex:/^\d{8}[A-Z]$/',
            'anos_experiencia' => 'sometimes|integer|max:80',
            'nombre' => 'sometimes|string|max:255',
            'nombreUsuario' => 'sometimes|string|max:255',
            'apellidos' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:usuarios,email,' . $empleado->usuario_id,
            'tlf' => 'sometimes|unique:empleados,tlf|digits_between:9,15|regex:/^\+?\d+$/',
            'direccion' => 'sometimes|string|max:255',
            'municipio' => 'sometimes|string|max:255',
            'provincia' => 'sometimes|string|max:255',
            'contrasena' => 'sometimes|string|min:8|confirmed',
        ], [
            'DNI.size' => 'El DNI debe tener 9 caracteres.',
            'DNI.regex' => 'El formato del DNI no es válido. Debe tener 8 números seguidos de una letra.',
            'DNI.unique' => 'El DNI ya está registrado en el sistema.',

            'anos_experiencia.integer' => 'Los años de experiencia deben ser un número entero.',
            'anos_experiencia.max' => 'Los años de experiencia no pueden ser superior a 80.',

            'nombre.string' => 'El nombre tiene que ser una cadena de texto.',

            'nombreUsuario.unique' => 'El nombre de usuario ya está en uso.',
            'nombreUsuario.string' => 'El nombre de usuario tiene que ser una cadena de texto.',

            'apellidos.string' => 'Los apellidos deben ser una cadena de texto.',

            'email.email' => 'El email debe ser una dirección válida.',
            'email.unique' => 'El email ya está en uso.',

            'tlf.digits_between' => 'El número de teléfono tiene que ser una cadena de dígitos entre 9 y 15.' ,
            'tlf.regex' => 'El número de teléfono sólo puede tener números y opcionalmente +.',
            'tlf.unique' => 'Este número de teléfono ya esta en uso.',

            'direccion.string' => 'La direccion debe ser una cadena de texto.',

            'municipio.string' => 'El municipio debe ser una cadena de texto.',

            'provincia.string' => 'La provincia debe ser una cadena de texto.',

            'contrasena.min' => 'La contraseña tiene que tener al menos 8 caracteres.',
            'contrasena.confirmed' => 'Las contraseñas deben ser iguales'
        ]);

        if ($request->has('DNI')) {
            $empleado->DNI = $validatedData['DNI'];
        }

        $empleado->update($validatedData);
        $usuario->update($validatedData);

        return response()->json($empleado->load('usuario'), 200);
    }

    /**
     * Elimina un empleado
     */
    public function delete(Request $request, $id) {
        $empleado = Empleado::find($id);
        $usuario = Usuario::find($empleado->usuario_id);

        if (!$empleado) {
            return response()->json(['message' => 'empleado no encontrado'], 404);
        }

        $empleado->delete();
        $usuario->delete();

        return response()->json(['message' => 'empleado eliminado correctamente'], 200);
    }
}
