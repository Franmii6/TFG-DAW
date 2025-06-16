<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empleado;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;

/**
 * Clase Controlador de Empleados
 */
class EmployeeController extends Controller
{
    /**
     * Añade un empleado junto a su usuario
     */
    public function add(Request $request) {
        $validatedData = $request->validate([
            'nombre'           => 'required|string|max:255',
            'nombreUsuario'    => 'required|string|max:255|unique:usuarios,nombreUsuario',
            'apellidos'        => 'required|string|max:255',
            'email'            => 'required|email|unique:usuarios,email',
            'tlf'              => 'required|unique:empleados,tlf|digits_between:9,15|regex:/^\+?\d+$/',
            'direccion'        => 'required|string|max:255',
            'municipio'        => 'required|string|max:255',
            'provincia'        => 'required|string|max:255',
            'contrasena'       => 'required|string|min:8|confirmed',
            'DNI'              => 'required|string|size:9|unique:empleados,DNI|regex:/^\d{8}[A-Z]$/',
            'anos_experiencia' => 'required|integer|max:80'
        ], [
            // mensajes de error personalizados...
        ]);

        // 1) Crear el usuario
        $usuario = Usuario::create([
            'nombre'         => $validatedData['nombre'],
            'nombreUsuario'  => $validatedData['nombreUsuario'],
            'email'          => $validatedData['email'],
            'contrasena'     => bcrypt($validatedData['contrasena']),
        ]);

        // 2) Crear el empleado
        $empleado = Empleado::create([
            'usuario_id'      => $usuario->id,
            'apellidos'       => $validatedData['apellidos'],
            'tlf'             => $validatedData['tlf'],
            'direccion'       => $validatedData['direccion'],
            'municipio'       => $validatedData['municipio'],
            'provincia'       => $validatedData['provincia'],
            'anos_experiencia'=> $validatedData['anos_experiencia'],
            'DNI'             => $validatedData['DNI'],
        ]);

        return response()->json($empleado->load('usuario'), 200);
    }

    /**
     * Creamos un empleado y lo asociamos a un usuario que ya existe en el sistema
     */
    public function addEmployee(Request $request, $id)
    {
        try {
            $usuario = Usuario::findOrFail($id);
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $validatedData = $request->validate([
            'apellidos'        => 'required|string|max:255',
            'tlf'              => 'required|unique:empleados,tlf|digits_between:9,15|regex:/^\+?\d+$/',
            'direccion'        => 'required|string|max:255',
            'municipio'        => 'required|string|max:255',
            'provincia'        => 'required|string|max:255',
            'DNI'              => 'required|string|size:9|unique:empleados,DNI|regex:/^\d{8}[A-Z]$/',
            'anos_experiencia' => 'required|integer|max:80',
            'especialidades'   => 'required|array',
            'especialidades.*' => 'exists:especialidades,id',
        ], [
            // mensajes de error personalizados...
        ]);

        // 1) Crear el empleado
        $empleado = Empleado::create([
            'usuario_id'      => $usuario->id,
            'apellidos'       => $validatedData['apellidos'],
            'tlf'             => $validatedData['tlf'],
            'direccion'       => $validatedData['direccion'],
            'municipio'       => $validatedData['municipio'],
            'provincia'       => $validatedData['provincia'],
            'anos_experiencia'=> $validatedData['anos_experiencia'],
            'DNI'             => $validatedData['DNI'],
        ]);

        // 2) Sincronizar las especialidades en la tabla pivote
        $empleado->especialidades()->sync($validatedData['especialidades']);

        return response()->json($empleado->load('usuario'), 200);
    }

    /**
     * Muestra los empleados
     */
    public function show(Request $request) {

        $request->validate([
            'especialidad_id' => 'sometimes|integer|exists:especialidads,id',
            'nombre'          => 'sometimes|string',
            'apellidos'       => 'sometimes|string',
            'tlf'             => 'sometimes|string',
            'DNI'             => 'sometimes|string',
            'skip'            => 'sometimes|integer',
            'take'            => 'sometimes|integer',
        ]);


        $query = Empleado::with('usuario', 'especialidades')
            ->select('empleados.*')
            ->join('usuarios', 'usuario_id', 'usuarios.id');

        // filtros opcionales...
        if ($request->has('especialidad_id')) {
            $espId = $request->get('especialidad_id');
            $query->whereHas('especialidades', function($q) use ($espId) {
                $q->where('especialidad_id', $espId);
            });
        }
        if ($request->get('nombre')) {
            $query->where('usuarios.nombre', 'LIKE', $request->get('nombre') . '%');
        }
        if ($request->get('apellidos')) {
            $query->where('empleados.apellidos', 'LIKE', $request->get('apellidos') . '%');
        }
        if ($request->get('tlf')) {
            $query->where('empleados.tlf', 'LIKE', '%' . $request->get('tlf') . '%');
        }
        if ($request->get('DNI')) {
            $query->where('empleados.DNI', 'LIKE', '%' . $request->get('DNI') . '%');
        }

        $empleados = $query->get();

        // paginación manual...
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

        if (! $empleado) {
            return response()->json([
                'message' => 'Empleado no encontrado, no está registrado en el sistema'
            ], 404);
        }

        return response()->json($empleado->load('usuario'), 200);
    }

    /**
     * Modifica un empleado
     */
    public function update(Request $request, $id) {
        $empleado = Empleado::find($id);
        if (! $empleado) {
            return response()->json(['message' => 'Empleado no encontrado'], 404);
        }
        $usuario = Usuario::find($empleado->usuario_id);

        $validatedData = $request->validate([
            'usuario_id'       => 'sometimes|exists:usuarios,id',
            'nombre'           => 'sometimes|string|max:255',
            'nombreUsuario'    => 'sometimes|string|max:255|unique:usuarios,nombreUsuario,' . $empleado->usuario_id,
            'email'            => 'sometimes|email|unique:usuarios,email,' . $empleado->usuario_id,
            'contrasena'       => 'sometimes|string|min:8|confirmed',
            'apellidos'        => 'sometimes|string|max:255',
            'tlf'              => 'sometimes|unique:empleados,tlf,' . $empleado->id . '|digits_between:9,15|regex:/^\+?\d+$/',
            'direccion'        => 'sometimes|string|max:255',
            'municipio'        => 'sometimes|string|max:255',
            'provincia'        => 'sometimes|string|max:255',
            'DNI'              => 'sometimes|string|size:9|unique:empleados,DNI,' . $empleado->id . '|regex:/^\d{8}[A-Z]$/',
            'anos_experiencia' => 'sometimes|integer|max:80',
            'especialidades'   => 'sometimes|array',
            'especialidades.*' => 'exists:especialidades,id',
        ], [
            // mensajes de error personalizados...
        ]);

        // 1) Actualizar usuario
        $usuarioData = Arr::only($validatedData, ['nombre','nombreUsuario','email','contrasena']);
        if (isset($usuarioData['contrasena'])) {
            $usuarioData['contrasena'] = bcrypt($usuarioData['contrasena']);
        }
        if (!empty($usuarioData)) {
            $usuario->update($usuarioData);
        }

        // 2) Actualizar empleado
        $empleadoData = Arr::only($validatedData, [
            'apellidos','tlf','direccion',
            'municipio','provincia','anos_experiencia','DNI'
        ]);
        if (!empty($empleadoData)) {
            $empleado->update($empleadoData);
        }

        // 3) Sincronizar especialidades si vienen
        if (array_key_exists('especialidades', $validatedData)) {
            $empleado->especialidades()->sync($validatedData['especialidades']);
        }

        return response()->json($empleado->load('usuario'), 200);
    }

    /**
     * Elimina un empleado
     */
    public function delete(Request $request, $id) {
        $empleado = Empleado::find($id);
        if (! $empleado) {
            return response()->json(['message' => 'Empleado no encontrado'], 404);
        }
        $usuario = Usuario::find($empleado->usuario_id);

        $empleado->delete();
        $usuario->delete();

        return response()->json(['message' => 'Empleado eliminado correctamente'], 200);
    }
}
