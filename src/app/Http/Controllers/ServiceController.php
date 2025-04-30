<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Servicio;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string|max:255',
            'duracion' => 'required|integer',
            'precio' => 'required|decimal:2',
        ], [
            'nomnbre.required' => 'El nombre es obligatorio',
            'nombre.string' => 'El nombre debe ser una cadena de texto.',

            'descripcion.required' => 'La descripcion es obligatorio',
            'descripcion.string' => 'La descripcion debe ser una cadena de texto.',

            'duracion.required' => 'La duracion es obligatoria',
            'duracion.integer' => 'La duracion debe ser un entero (min)',

            'precio.required' => 'El precio es obligatorio',
            'precio.decimal' => 'El precio debe ser decimal'
        ]);

        $servicio = Servicio::create([
            'nombre' => $validatedData['nombre'],
            'descripcion' => $validatedData['descripcion'],
            'duracion' => $validatedData['duracion'],
            'precio' => $validatedData['precio'],
        ]);

        return response()->json($servicio, 201);
    }

    /**
     * Muestra los servicios
     */
    public function show()
    {
        $servicios = Servicio::all();

        // Verificar si no hay servicios registrados
        if ($servicios->isEmpty()) {
            return response()->json(['message' => 'No se han encontrado servicios registrados'], 404);
        }

        return response()->json($servicios, 200);
    }

    /**
     * Obtiene un servicio
     */
    public function getService($id)
    {
        try {
            $servicio = Servicio::findOrFail($id);
        }
        catch (ModelNotFoundException){
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
            return response()->json(['message' => 'servicio no encontrado'], 404);
        }


        $validatedData = $request->validate([
            'servicio_id' => 'sometimes|exists:servicios,id,' . $id,
            'nombre' => 'sometimes|string|max:255',
            'descripcion' => 'sometimes|string|max:255',
            'duracion' => 'sometimes|integer|',
            'precio' => 'sometimes|decimal:2',
        ], [
            'nombre.string' => 'El nombre debe ser una cadena de texto',

            'descripcion.string' => 'La descripcion debe ser una cadena de texto',

            'duracion.integer' => 'La duración debe ser un entero (min)',

            'precio.decimal' => 'El precio debe ser un decimal'
        ]);

        $servicio->update($validatedData);

        return response()->json($servicio, 201);
    }
    /**
     * Elimina un servicio
     */
    public function delete($id)
    {
        try {
            $servicio = Servicio::findOrFail($id);
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'servicio no encontrado'], 404);
        }

        $servicio->delete();

        return response()->json(['message' => 'Servicio eliminado correctamente'], 200);
    }
}
