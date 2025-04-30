<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cita;
use App\Models\CitaServicio;
use App\Models\Cliente;
use App\Models\Contrato;
use App\Models\Servicio;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use function Pest\Laravel\json;

/**
 * Clase Controlador de Citas
 */
class AppointmentController extends Controller
{

    /**
     * Añade una cita
     * 
     * Asocia el o los servicios a la cita
     */
    public function add(Request $request)
    {

        $validatedData = $request->validate([
            'contrato_id' => 'required|integer', //si no existe el contrato manejar error
            'cliente_id' => 'required|integer',
            'empleado_id' => 'required|integer',
            'arrayServicios' => 'required|array',
            'fecha' => 'required|date',
            'estado' => 'required|in:pendiente,cancelado,completado',
            'numero_de_atenciones' => 'required|integer|max:50',
        ], [
            'contrato_id.required' => 'El contrato es obligatorio',
            'contrato_id.integer' => 'El contrato debe ser un numero entero',

            'cliente_id.required' => 'El cliente es obligatorio',
            'cliente_id.integer' => 'El cliente debe ser un numero entero',

            'empleado_id.required' => 'El empleado es obligatorio',
            'empleado_id.integer' => 'El empleado debe ser un numero entero',

            'arrayServicios.required' => 'Es obligatorio poner los servicios',
            'arrayServicios.array' => 'Los servicios deben incluirse como un array []',

            'fecha.required' => 'La fecha es obligatoria',
            'fecha.date' => 'La fecha tiene que estar en formato yyyy-mm-dd',

            'estado.required' => 'El estado de la cita es obligatorio (pendiente, cancelado, completado)',
            'estado.in' => 'El estado de la cita debe ser: pendiente, cancelado o completado',

            'numero_de_atenciones.required' => 'El numero de atenciones es obligatorio',
            'numero_de_atenciones.max' => 'El maximo de numero de atenciones es 50',
            'numero_de_atenciones.integer' => 'el numero de atenciones debe ser un numero entero'
        ]);

        $cita = Cita::create([
            'cliente_id' => $validatedData['cliente_id'],
            'contrato_id' => $validatedData['contrato_id'],
            'empleado_id' => $validatedData['empleado_id'],
            'fecha' => $validatedData['fecha'],
            'estado' => $validatedData['estado'],
            'numero_de_atenciones' => $validatedData['numero_de_atenciones']
        ]);

        foreach ($validatedData['arrayServicios'] as $idServicio) {
            $citaServicio = CitaServicio::create([
                'cita_id' => $cita->id,
                'servicio_id' => $idServicio,
            ]);
        }

        return response()->json($cita->load('servicios'), 201);
    }
    /**
     * Muestra las citas con sus servicios
     */
    public function show(Request $request)
    {
        // skip y take para limitar las lineas mostradas
        if ($request->get('skip') > Cita::count()) {
            return response()->json(['Message' => 'skip supera el número de lineas en tabla'], 400);
        }

        $request->validate([
            'nombre_cliente' => 'sometimes|string',
            'id_empleado' => 'sometimes|integer',
            'fecha' => 'sometimes|date',
            'estado' => 'sometimes|in:pendiente,cancelado,completado',
        ]);

        $query = Cita::with('servicios')
            ->select('citas.*')
            ->join('clientes', 'cliente_id', 'clientes.id')
            ->join('empleados', 'empleado_id', 'empleados.id')
            ->join('usuarios', 'clientes.usuario_id', 'usuarios.id');

        if ($request->get('nombre_cliente')) {
            $query = $query->where('usuarios.nombre', 'LIKE', $request->get('nombre_cliente') . '%');
        }
        if ($request->get('id_empleado')) {
            $query = $query->where('empleados.id', '=', $request->get('id_empleado'));
        }
        if ($request->get('fecha')) {
            $query = $query->where('citas.fecha', 'LIKE', $request->get('fecha') . '%');
        }
        if ($request->get('estado')) {
            $query = $query->where('citas.estado', 'LIKE', $request->get('estado') . '%');
        }

        $citas = $query->get();

        if ($request->get('skip')) {
            $citas = $citas->skip((int)$request->get('skip'));
        }
        if ($request->get('take')) {
            $citas = $citas->take((int)$request->get('take'));
        } else {
            $citas = $citas->take(Cita::count());
        }

        if ($citas->isEmpty()) {
            return response()->json(['message' => 'No hay citas registradas'], 404);
        }

        return response()->json($citas, 200);
    }

    /**
     * Obtiene una cita con su o sus servicios
     */
    public function getAppointment($idCita, $withServicios)
    {
        try {
            $cita = Cita::FindOrFail($idCita);
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'cita no encontrada'], 404);
        }

        //$withServicios es un booleano para indicar si se quieren ver los servicios o no de cada cita
        $withServicios == "true" ? $return = response()->json($cita->load('servicios'), 200) :
            $return = response()->json($cita, 200);

        return $return;

        return $return;
    }

    /**
     * Modifica una cita con su o sus servicios
     */
    public function update(Request $request, $idCita)
    {
        try {
            $cita = Cita::findOrFail($idCita);
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Cita no encontrada'], 404);
        }

        $citaServicios = CitaServicio::where('cita_id', '=', $idCita)->get(); //No requiere control de errores ya que si idCita fuera erroneo habria saltaado excepcion antes, y si no hay lineas en citaServicios tampoco pasa nada porque se van a crear nuevas ahora
        $arrayServicios = [];

        $validatedData = $request->validate([
            'contrato_id' => 'sometimes|integer',
            'cliente_id' => 'sometimes|integer',
            'empleado_id' => 'sometimes|integer',
            'arrayServicios' => 'sometimes|array',
            'fecha' => 'sometimes|date_format:Y-m-d', //Formato Y-m-d
            'estado' => 'sometimes|in:pendiente,cancelado,completado',
            'numero_de_atenciones' => 'sometimes|integer|max:50', //Podemos corregir datos de una cita sin haber hecho una atencion
        ], [
            'contrato_id.integer' => 'El id de contrato debe ser un numero entero',
            'cliente_id.integer' => 'El id de cliente debe ser un numero entero',
            'empleado_id.integer' => 'El id de empleado debe ser un numero entero',

            'arrayServicios.array' => 'Los servicios deben incluirse en un formato de array []',
            'fecha.date_format' => 'La fecha debe incluirse con el formato yyy-mm-dd',
            'estado.in' => 'El estado de la cita debe ser pendiente, cancelado o completado',
            'numero_de_atenciones.integer' => 'El numero de atenciones debe ser un numero entero',
            'numero_de_atenciones.max' => 'El maximo numero de atenciones es 50'
        ]);

        // Si esta vacio usa ya las lineas de citaServicios ya creadas, sino las borra y crea nuevas
        if (isset($validatedData['arrayServicios'])) {
            // Comprobamos si cada servicio existe
            foreach ($validatedData['arrayServicios'] as $idServicio) {
                if (!Servicio::find($idServicio)) {
                    return response()->json(['message' => "El servicio con id $idServicio no existe"], 404);
                }
            }

            foreach ($citaServicios as $servicio) {
                $servicio->delete();
            }
            foreach ($validatedData['arrayServicios'] as $idServicio) {
                $citaServicio = CitaServicio::create([
                    'cita_id' => $cita->id,
                    'servicio_id' => $idServicio,
                ]);
            }
        } else {
            foreach ($citaServicios as $servicio) {
                array_push($arrayServicios, $servicio->id);
            }
            $validatedData['arrayServicios'] = $arrayServicios;
        }

        $cita->update($validatedData);

        return response()->json($cita->load('servicios'), 201);
    }

    /**
     * Elimina una cita con su o sus servicios
     */
    public function delete($idCita)
    {
        try {
            $cita = Cita::FindOrFail($idCita);
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Cita no encontrada'], 404);
        }

        $cita->delete();

        return response()->json(['message' => 'Cita eliminada correctamente'], 200);
    }
}
