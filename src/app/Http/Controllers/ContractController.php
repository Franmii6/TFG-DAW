<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contrato;
use App\Models\Cliente;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * clase Controlador de Contrato
 */
class ContractController extends Controller
{

    /**
     * Añade un contrato a un cliente
     */
    public function add($idCliente, Request $request)
    {
        $validatedData = $request->validate([
            'numero_de_atenciones' => 'required|integer|max:50',
            'numero_de_atenciones_realizadas' => 'sometimes|integer|max:50',
            'fecha_inicio' => 'sometimes|date', //Por defecto es la de hoy
            'fecha_fin' => 'sometimes|date',    //Por defecto es la de dentro de 1 año
        ], [
            'numero_de_atenciones.required' => 'El numero de atenciones es obligatorio',
            'numero_de_atenciones.integer' => 'El numero de atenciones debe ser un entero',

            'numero_de_atenciones_realizadas.integer' => 'El numero de atenciones realizadas debe ser un entero',
            'numero_de_atenciones_realizadas.max' => 'El maximo de atenciones realizadas es 50',

            'fecha_inicio.date' => 'La fecha de inicio debe tener formato de fecha. Por defecto, es la de hoy.',

            'fecha_fin.date' => 'La fecha de fin debe tener formato de fecha. Por defecto, es la de dentro de 1 año.'
        ]);

        // Asignar 0 por defecto si no se incluye 'numero_de_atenciones_realizadas'
        $numeroDeAtencionesRealizadas = $validatedData['numero_de_atenciones_realizadas'] ?? 0;

        // Asignar la fecha de hoy si no se incluye 'fecha_inicio'
        $fechaInicio = $validatedData['fecha_inicio'] ?? now()->format('Y-m-d');

        // Asignar la fecha de dentro de un año si no se incluye 'fecha_fin'
        $fechaFin = $validatedData['fecha_fin'] ?? now()->addYear()->format('Y-m-d');

        $contrato = Contrato::create([
            'cliente_id' => $idCliente,
            'numero_de_atenciones' => $validatedData['numero_de_atenciones'],
            'numero_de_atenciones_realizadas' => $numeroDeAtencionesRealizadas,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
        ]);

        return response()->json($contrato, 201);
    }

    /**
     * Muestra todos los contratos
     */
    public function showAll()
    {
        $contratos = Contrato::all();

        // Verificar si no hay contratos registrados
        if ($contratos->isEmpty()) {
            return response()->json(['message' => 'No se han encontrado contratos registrados'], 404);
        }

        return response()->json($contratos, 200);
    }

    /**
     * Muestra los contratos de un cliente
     */
    public function show($idCliente)
    {
        $resolution = [];

        try {
            $contratos = Contrato::where('cliente_id', '=', $idCliente)->get();

            foreach ($contratos as $contrato) {
                array_push($resolution, Contrato::find($contrato->id));
            }

            if ($resolution == []) {
                throw new ModelNotFoundException();
            }
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Cita no encontrada'], 404);
        }

        return response()->json($contratos, 200);
    }

    /**
     * Obtiene un contrato de un cliente
     */
    public function getContract($idCliente, $idContrato)
    {
        try {
            $contrato = Contrato::findOrFail($idContrato);
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Contrato no encontrado'], 404);
        }

        return response()->json($contrato, 200);
    }

    /**
     * Modifica un contrato de un cliente
     */
    public function update(Request $request, $idContrato)
    {
        try {
            $contrato = Contrato::findOrFail($idContrato);
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'contrato no encontrado'], 404);
        }

        $validatedData = $request->validate([
            'contrato_id' => 'sometimes|exists:contratos,id,' . $idContrato,
            'numero_de_atenciones' => 'sometimes|integer|max:50',
            'numero_de_atenciones_realizadas' => 'sometimes|integer|max:50',
            'fecha_inicio' => 'sometimes|date',
            'fecha_fin' => 'sometimes|date',
        ], [
            'numero_de_atenciones.integer' => 'El numero de atenciones debe ser un entero',
            'numero_de_atenciones.max' => 'El maximo de numero de atenciones es de 50',

            'numero_de_atenciones_realizadas.integer' => 'El numero de atenciones realizadas debe ser un entero',
            'numero_de_atenciones_realizadas.max' => 'El maximo de numero de atenciones realizadas es de 50',

            'fecha_inicio.date' => 'La fecha de inicio debe tener formato de fecha.',

            'fecha_fin.date' => 'La fecha de fin debe tener formato de fecha.'
        ]);

        $contrato->update($validatedData);

        return response()->json($contrato, 201);
    }

    /**
     * Elimina un contrato de un cliente
     */
    public function delete($idContrato)
    {
        try {
            $contrato = Contrato::findOrFail($idContrato);
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Contrato no encontrado'], 404);
        }

        $contrato->delete();

        return response()->json(['message' => 'Contrato eliminado correctamente'], 200);
    }
}
