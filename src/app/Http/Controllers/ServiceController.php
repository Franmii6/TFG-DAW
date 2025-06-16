<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Servicio;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Empleado;

/**
 * Clase Controlador de Servicios
 */
class ServiceController extends Controller
{
    /**
     * Añade un servicio
     */
    public function add(Request $request)
    {
        $validatedData = $request->validate([
            'nombre'          => 'required|string|max:255',
            'descripcion'     => 'required|string|max:255',
            'duracion'        => 'required|integer',
            'precio'          => 'required|numeric|min:0',
            'especialidad_id' => 'required|exists:especialidades,id',
        ], [
            'nombre.required'      => 'El nombre es obligatorio.',
            'nombre.string'        => 'El nombre debe ser una cadena de texto.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.string'   => 'La descripción debe ser una cadena de texto.',
            'duracion.required'    => 'La duración es obligatoria.',
            'duracion.integer'     => 'La duración debe ser un entero (min).',
            'precio.required'      => 'El precio es obligatorio.',
            'precio.numeric'       => 'El precio debe ser un número.',
            'especialidad_id.required' => 'La especialidad es obligatoria.',
            'especialidad_id.exists'   => 'La especialidad seleccionada no existe.',
        ]);

        $servicio = Servicio::create([
            'nombre'          => $validatedData['nombre'],
            'descripcion'     => $validatedData['descripcion'],
            'duracion'        => $validatedData['duracion'],
            'precio'          => $validatedData['precio'],
            'especialidad_id' => $validatedData['especialidad_id'],
        ]);

        return response()->json($servicio, 201);
    }

    /**
     * Muestra los servicios
     */
    public function show()
    {
        // 1) Traemos todos los servicios junto con su especialidad
        $servicios = Servicio::with('especialidad')->get();

        // 2) Para cada servicio, cargamos sus empleados según el especialidad_id
        $servicios->transform(function($servicio) {
            $espId = $servicio->especialidad_id;

            // Buscamos todos los empleados que en la pivot tengan ese $espId
            // (es decir, empleados que sepan hacer la especialidad asociada a este servicio)
            $empleados = Empleado::whereHas('especialidades', function($q) use ($espId) {
                $q->where('especialidad_id', $espId);
            })
            ->with('usuario') // para que e.usuario.nombre esté disponible
            ->get();

            // Asignamos esa colección 'empleados' al servicio
            $servicio->setRelation('empleados', $empleados);

            return $servicio;
        });

        return response()->json($servicios, 200);
    }

    /**
     * Obtiene un servicio
     */
    public function getService($id)
    {
        try {
            $servicio = Servicio::findOrFail($id);
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Servicio no encontrado'], 404);
        }

        return response()->json($servicio, 200);
    }

    /**
     * Modifica un servicio
     */
    public function update(Request $request, $id)
    {
        try {
            $servicio = Servicio::findOrFail($id);
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Servicio no encontrado'], 404);
        }

        $validatedData = $request->validate([
            'nombre'          => 'sometimes|string|max:255',
            'descripcion'     => 'sometimes|string|max:255',
            'duracion'        => 'sometimes|integer',
            'precio'          => 'sometimes|numeric|min:0',
            'especialidad_id' => 'sometimes|exists:especialidads,id',
        ], [
            'nombre.string'    => 'El nombre debe ser una cadena de texto.',
            'descripcion.string' => 'La descripción debe ser una cadena de texto.',
            'duracion.integer' => 'La duración debe ser un entero (min).',
            'precio.numeric'   => 'El precio debe ser un número.',
            'especialidad_id.exists' => 'La especialidad seleccionada no existe.',
        ]);

        $servicio->update($validatedData);

        return response()->json($servicio, 200);
    }

    /**
     * Elimina un servicio
     */
    public function delete($id)
    {
        try {
            $servicio = Servicio::findOrFail($id);
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Servicio no encontrado'], 404);
        }

        $servicio->delete();

        return response()->json(['message' => 'Servicio eliminado correctamente'], 200);
    }
}
