<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empleado;
use App\Models\Especialidad;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Clase controlador Especialidad Por Empleado
 */
class EmployeeSpecialtyController extends Controller
{
    /**
     * Asigna especialidades a un empleado
     */
    public function assign(Request $request, $empleadoId)
    {
        $empleado = Empleado::findOrFail($empleadoId);
        $especialidadIds = $request->input('especialidad_ids'); 

        // Verificar si el campo especialidad_ids está vacío o no se envió
        if (is_null($especialidadIds) || (is_array($especialidadIds) && empty($especialidadIds))) {
            return response()->json(['message' => 'Añade alguna especialidad.'], 400);
        }

        // Verificar si cada especialidad existe
        $especialidadesNoExistentes = [];
        foreach ((array)$especialidadIds as $id) { 
            if (!Especialidad::find($id)) {
                $especialidadesNoExistentes[] = $id;
            }
        }

        // Si hay especialidades que no existen, retornar un mensaje de error
        if (!empty($especialidadesNoExistentes)) {
            return response()->json([
                'message' => 'Las siguientes especialidades no existen: ' . implode(', ', $especialidadesNoExistentes)
            ], 404);
        }

        // Asignar especialidades al empleado sin eliminar las existentes
        $empleado->especialidades()->syncWithoutDetaching($especialidadIds);
        
        // Retornar las especialidades del empleado
        return response()->json($empleado->especialidades, 200);
    }

    /**
     * Lista especialidades de un empleado
     */
    public function list($empleadoId)
    {
        try {
            $empleado = Empleado::findOrFail($empleadoId);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Empleado no encontrado con ID ' . $empleadoId], 404);
        }

        $especialidades = $empleado->especialidades;

        // Verificar si el empleado tiene especialidades asignadas
        if ($especialidades->isEmpty()) {
            return response()->json(['message' => 'No hay especialidades asignadas al empleado con ID ' . $empleadoId], 404);
        }

        // Retornar las especialidades del empleado
        return response()->json($especialidades, 200);
    }

    /**
     * Elimina una especialidad de un empleado
     */
    public function delete($empleadoId, $especialidadId)
    {
        $empleado = Empleado::findOrFail($empleadoId);
        
        // Verificar si la especialidad está asignada al empleado
        if ($empleado->especialidades()->where('especialidads.id', $especialidadId)->exists()) {
            // Eliminar la especialidad
            $empleado->especialidades()->detach($especialidadId);
            return response()->json(['message' => 'Especialidad ID ' . $especialidadId . ' eliminada correctamente del empleado ID ' . $empleadoId], 200);
        } else {
            // La especialidad no existe para el empleado
            return response()->json(['message' => 'No existe la especialidad ID ' . $especialidadId . ' en el empleado ID ' . $empleadoId], 404);
        }
    }
}
