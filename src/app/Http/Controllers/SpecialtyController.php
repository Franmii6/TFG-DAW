<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Especialidad;

/**
 * Clase Controlador de Especialidades
 */
class SpecialtyController extends Controller
{
    /**
     * Añade una especialidad
     */
    public function add(Request $request) {
        $request->validate(
            ['nombre' => 'required|string|unique:especialidads'],
            ['nombre.string' => 'El nombre de la especialidad debe ser una cadena de texto.',
            'nombre.unique' => 'Ya existe esta especialidad.']
        );
        return Especialidad::create(['nombre' => $request->nombre]);
    }

    /**
     * Muestra las especialidades
     */
    public function show() {
        return Especialidad::all();
    }

    /**
     * Obtiene una especialidad
     */
    public function getSpecialty($id) {
        return Especialidad::findOrFail($id);
    }

    /**
     * Modifica una especialidad
     */
    public function update(Request $request, $id) {
        $especialidad = Especialidad::findOrFail($id);
        $request->validate(['nombre' => 'required|string|unique:especialidads,nombre,' . $id],
        [
            'nombre.string' => 'El nombre tiene que ser una cadena de texto.',
            'nombre.unique' => 'Esta especialidad ya existe.'
        ]);
        $especialidad->update(['nombre' => $request->nombre]); 
        return $especialidad;
    }

    /**
     * Elimina una especialidad
     */
    public function delete($id)
    {
        try {
            $especialidad = Especialidad::findOrFail($id);
            
            $especialidadName = $especialidad->nombre;  

            $especialidad->delete(); 
            
            return response()->json([
                'message' => 'Especialidad eliminada',
                'especialidad' => $especialidadName
            ], 200); 
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Especialidad no encontrada'], 404);
        }
    }


}
