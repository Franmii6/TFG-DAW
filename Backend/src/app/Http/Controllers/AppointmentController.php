<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cita;
use App\Models\CitaServicio;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Carbon; // Librería de php para el manejo de horas
use App\Models\Servicio;
use Illuminate\Support\Facades\Auth;

/**
 * Clase Controlador de Citas
 */
class AppointmentController extends Controller
{
    /**
     * Añade una cita
     * * Asocia el servicio a la cita
     */
    public function add(Request $request)
    {
        $validatedData = $request->validate([
            'servicio_id'          => 'required|integer|exists:servicios,id',
            'empleado_id'          => 'required|integer|exists:empleados,id',
            'fecha'                => 'required|date_format:Y-m-d H:i:s',
            'estado'               => 'required|in:pendiente,cancelada,completada',
            'numero_de_atenciones' => 'required|integer|max:50',
        ], [
            'servicio_id.required'      => 'El servicio es obligatorio',
            'servicio_id.exists'        => 'El servicio no existe',
            'empleado_id.required'      => 'El empleado es obligatorio',
            'empleado_id.exists'        => 'El empleado no existe',
            'fecha.required'            => 'La fecha es obligatoria',
            'fecha.date_format'         => 'La fecha debe tener formato Y-m-d H:i:s',
            'estado.required'           => 'El estado es obligatorio',
            'estado.in'                 => 'El estado debe ser: pendiente, cancelada o completada',
            'numero_de_atenciones.max'  => 'El máximo número de atenciones es 50',
            'numero_de_atenciones.integer' => 'El número de atenciones debe ser un entero',
        ]);

        // 2) Obtener usuario y su cliente
        $usuario = Auth::user();
        if (! $usuario || ! $usuario->cliente) {
            return response()->json(['message' => 'Cliente no autenticado'], 401);
        }
        $cliente = $usuario->cliente;

        // 3) Verificar (o crear) el contrato asociado al cliente:
        //    Si el cliente ya tiene un contrato, lo usamos. Si no, lo creamos con 1 atención por defecto.
        if ($cliente->contrato) {
            $contratoId = $cliente->contrato->id;
        } else {
            // Creamos un contrato nuevo con 1 atención inicial
            $nuevoContrato = $cliente->contrato()->create([
                'numero_de_atenciones' => 10,
                'numero_de_atenciones_realizadas' => 0,
                'estado' => 'activo',
                // Si tu migración de contratos necesita fechas de inicio/fin, las pones aquí.
            ]);
            $contratoId = $nuevoContrato->id;
        }

        // 4) Crear la cita
        $cita = Cita::create([
            'cliente_id'           => $cliente->id,
            'contrato_id'          => $contratoId,
            'empleado_id'          => $request->input('empleado_id', null), 
            'fecha'                => $validatedData['fecha'],
            'estado'               => $validatedData['estado'],
            'numero_de_atenciones' => $validatedData['numero_de_atenciones'],
            'has_comment'          => false, // Nueva cita, no evaluada aún
        ]);

        // 5) Asociar el servicio a la cita
        CitaServicio::create([
            'cita_id'     => $cita->id,
            'servicio_id' => $validatedData['servicio_id'],
        ]);

        return response()->json($cita->load('servicios'), 201);
    }

    /**
     * Muestra las citas con sus servicios
     */
    public function show(Request $request)
    {
        // skip y take para limitar las líneas mostradas
        if ($request->get('skip') > Cita::count()) {
            return response()->json(['Message' => 'skip supera el número de líneas en tabla'], 400);
        }

        $request->validate([
            'nombre_cliente' => 'sometimes|string',
            'id_empleado'    => 'sometimes|integer',
            'fecha'          => 'sometimes|date',
            'estado'         => 'sometimes|in:pendiente,cancelada,completada',
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
            // Cargar la cita y sus relaciones de servicios y empleado con usuario
            $cita = Cita::with(['servicios', 'empleado.usuario'])->findOrFail($idCita);
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Cita no encontrada'], 404);
        }

        // Si se pide con servicios, los cargamos. Si no, solo la cita.
        // La lógica $withServicios == "true" puede ser más sencilla si siempre cargas lo necesario.
        // Si ya estás haciendo with(['servicios', 'empleado.usuario']) arriba, $withServicios no es tan crítico.
        $return = response()->json($cita, 200);

        return $return;
    }

    /**
     * Modifica una cita
     * * Ahora cada cita lleva un único servicio, y al cambiar el estado a "completada"
     * se incrementa en 1 el contador de atenciones realizadas en el contrato asociado.
     */
    public function update(Request $request, $idCita)
    {
        // 1) Intentamos cargar la cita, si no existe devolvemos 404
        try {
            $cita = Cita::findOrFail($idCita);
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Cita no encontrada'], 404);
        }

        // 2) Validamos sólo los campos que pueden cambiar
        $validatedData = $request->validate([
            'empleado_id' => 'sometimes|integer',
            'fecha'       => 'sometimes|date_format:Y-m-d H:i:s',
            'estado'      => 'sometimes|in:pendiente,cancelada,completada',
        ], [
            'empleado_id.integer' => 'El id de empleado debe ser un número entero',
            'fecha.date_format'   => 'La fecha debe tener formato Y-m-d H:i:s',
            'estado.in'           => 'El estado de la cita debe ser: pendiente, cancelada o completada',
        ]);

        // 3) Guardamos el estado anterior para detectar transiciones
        $oldEstado = $cita->estado;

        // 4) Actualizamos la cita con los nuevos datos
        $cita->update($validatedData);

        // 5) Si el estado ha cambiado a 'completada', incrementamos el contrato
        if (isset($validatedData['estado'])
            && $oldEstado !== 'completada'
            && $cita->estado === 'completada'
        ) {
            // Relación belongsTo: cada cita conoce su contrato
            $contrato = $cita->contrato;
            if ($contrato) {
                $contrato->increment('numero_de_atenciones_realizadas');

                // Si se agotan atenciones, finalizamos:
                if ($contrato->numero_de_atenciones_realizadas >= $contrato->numero_de_atenciones) {
                    $contrato->update(['estado' => 'finalizado']);
                }
            }
        }

        // 6) Devolver la cita actualizada
        return response()->json($cita, 200);
    }

    /**
     * Elimina una cita con su o sus servicios
     */
    public function delete($idCita)
    {
        try {
            $cita = Cita::findOrFail($idCita);
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Cita no encontrada'], 404);
        }

        $cita->delete();

        return response()->json(['message' => 'Cita eliminada correctamente'], 200);
    }

    /**
     * Obtener franjas libres y empleados disponibles por franja
     */
    public function getSlots(Request $request)
    {
        // 1) Validar entrada
        $request->validate([
            'fecha'       => 'required|date_format:Y-m-d',
            'servicio_id' => 'required|integer|exists:servicios,id',
        ]);

        $fecha       = $request->input('fecha');
        $servicio_id = $request->input('servicio_id');

        // 2) Leer configuración de horario
        $opensAt      = config('booking.opens_at');      // ej. "09:00"
        $closesAt     = config('booking.closes_at');     // ej. "20:00"
        $slotInterval = config('booking.slot_interval'); // ej. 30 (minutos)

        if (is_null($opensAt) || is_null($closesAt) || is_null($slotInterval)) {
            return response()->json(['message' => 'Error en configuración de franjas'], 500);
        }

        // 3) Generar todas las franjas entre apertura y cierre
        $start    = Carbon::parse($opensAt);
        $end      = Carbon::parse($closesAt);
        $allSlots = [];
        while ($start->lt($end)) {
            $allSlots[] = $start->format('H:i');
            $start->addMinutes($slotInterval);
        }

        // 4) Recoger todas las citas de ese día y agrupar horas ocupadas por empleado
        $citasHoy = Cita::whereDate('fecha', $fecha)->get();
        $ocupadoPorEmpleado = [];
        foreach ($citasHoy as $cita) {
            // $cita->fecha es Carbon; extraemos solo la hora "HH:MM"
            $hora  = $cita->fecha->format('H:i');
            $empId = $cita->empleado_id;
            if (!isset($ocupadoPorEmpleado[$empId])) {
                $ocupadoPorEmpleado[$empId] = [];
            }
            $ocupadoPorEmpleado[$empId][] = $hora;
        }

        // 5) Obtener empleados cualificados para el servicio
        // Cargamos con eager‐load para traer la especialidad junto a sus empleados y cada empleado trae su usuario
        $servicio = Servicio::with('especialidad.empleados.usuario')->find($servicio_id);
        if (!$servicio || !$servicio->especialidad) {
            return response()->json(['message' => 'Servicio sin especialidad asociada'], 500);
        }
        // Todos los empleados que pueden hacer este servicio según pivote
        $empleadosCalificados = $servicio->especialidad->empleados;

        // 6) Para cada franja, comprobamos si al menos uno de los empleados calificados está libre
        $availableSlots = [];
        $availability   = [];

        // Si la fecha es hoy, guardamos la hora actual para filtrar franjas pasadas
        $isToday = ($fecha === Carbon::now()->toDateString());
        $now     = Carbon::now();

        foreach ($allSlots as $hora) {
            // 6.1) Si es hoy, descartamos franjas pasadas
            if ($isToday) {
                $slotTime = Carbon::parse("$fecha $hora");
                if ($slotTime->lte($now)) {
                    continue; // saltamos esta franja porque ya ha pasado o es igual a la hora actual
                }
            }

            // 6.2) Ahora comprobamos, de los empleados calificados, quiénes están libres a esta $hora
            $libres = [];
            foreach ($empleadosCalificados as $emp) {
                $ocupadas = $ocupadoPorEmpleado[$emp->id] ?? [];
                if (!in_array($hora, $ocupadas)) {
                    $libres[] = [
                        'id'   => $emp->id,
                        'name' => $emp->usuario->nombre . ' ' . $emp->apellidos,
                    ];
                }
            }

            // 6.3) Solo si hay al menos un empleado libre, incluimos la franja en availableSlots
            if (!empty($libres)) {
                $availableSlots[]      = $hora;
                $availability[$hora]   = $libres;
            }
        }

        // 7) Devolver las franjas + los empleados libres por franja
        // Esta es la parte que ha causado el error.
        // Se ha corregido para devolver la estructura que `disponibilidadSlotsServicio` realmente construye.
        return response()->json([
            'slots'        => $availableSlots,
            'availability' => $availability
        ], 200);
    }

    /**
     * Devuelve todas las citas (con servicios) del cliente autenticado para poder evaluar la cita y mostrarlas en la página de perfil
     */
    public function indexPorCliente()
    {
        $usuario = Auth::user();
        if (! $usuario || ! $usuario->cliente) {
            return response()->json(['message' => 'Cliente no autenticado'], 401);
        }

        $clienteId = $usuario->cliente->id;

        // Auto-completar citas pasadas pendientes
        $now = Carbon::now();
        $pendientes = Cita::where('cliente_id', $clienteId)
                          ->where('estado', 'pendiente')
                          ->where('fecha', '<=', $now)
                          ->get();
        foreach ($pendientes as $citaPendiente) {
            $citaPendiente->estado = 'completada';
            $citaPendiente->save();
            $contrato = $citaPendiente->contrato;
            if ($contrato) {
                $contrato->increment('numero_de_atenciones_realizadas');
                if ($contrato->numero_de_atenciones_realizadas >= $contrato->numero_de_atenciones) {
                    $contrato->update(['estado' => 'finalizado']);
                }
            }
        }

        
        // Cargar las citas junto a los servicios, empleado y el usuario del empleado (relaciones Eloquent)
        // El campo 'has_comment' se cargará automáticamente al ser parte del modelo Cita
        $citas = Cita::with(['servicios', 'empleado.usuario'])
        ->where('cliente_id', $clienteId)
        ->orderBy('fecha', 'desc')
        ->get();

        // 3) Respuesta unificada
        return response()->json([
            'data'    => $citas,
            'message' => $citas->isEmpty()
                ? 'No tienes citas registradas aún.'
                : 'Estas son tus últimas citas.'
        ], 200);
    }

    /**
     * Devuelve las franjas horarias libres para un servicio y fecha concreta,
     * teniendo en cuenta la duración del servicio y las citas ya reservadas
     * de cada empleado calificado.
     *
     * Request:
     * - servicio_id (int)
     * - fecha      (Y-m-d)
     *
     * Response:
     * [
     * "id_empleado" => { "name": "Nombre Empleado", "slots": ["09:00", "09:30"] },
     * ...
     * ]
     */
    public function disponibilidadSlotsServicio(Request $request, $servicio)
    {
        // 1) Validar y obtener datos
        $request->merge(['servicio_id' => $servicio]);
        $request->validate([
            'servicio_id' => 'required|integer|exists:servicios,id',
            'fecha'       => 'required|date_format:Y-m-d',
        ]);
        $fecha    = $request->input('fecha');
        $servicio = Servicio::with('especialidad.empleados.usuario')
                            ->findOrFail($request->input('servicio_id'));
        $duracion = (int) $servicio->duracion;
        $empleados = $servicio->especialidad->empleados;
        if ($empleados->isEmpty()) {
            return response()->json(['message' => 'No hay empleados para ese servicio'], 404);
        }

        // 2) Citas del día y ocupación por empleado
        $citasHoy = Cita::whereDate('fecha', $fecha)->get();
        $ocupadoPorEmpleado = [];
        foreach ($citasHoy as $cita) {
            $hora  = $cita->fecha->format('H:i');
            $ocupadoPorEmpleado[$cita->empleado_id][] = $hora;
        }

        // 3) Horario y breaks
        $opensAt    = Carbon::parse(config('booking.opens_at'));
        $closesAt   = Carbon::parse(config('booking.closes_at'));
        $breakStart = Carbon::parse(config('booking.break_start'));
        $breakEnd   = Carbon::parse(config('booking.break_end'));
        $isToday = ($fecha === Carbon::now()->toDateString());
        $now     = Carbon::now();

        // 4) Para cada empleado, generamos sus slots libres
        $slotsPorEmpleado = [];
        foreach ($empleados as $emp) {
            $puntero = Carbon::parse("$fecha {$opensAt->format('H:i')}");
            $finDia  = Carbon::parse("$fecha {$closesAt->format('H:i')}")->subMinutes($duracion);
            $slotsPorEmpleado[$emp->id] = [
                'name'  => "{$emp->usuario->nombre} {$emp->apellidos}", // Asumiendo que el nombre de usuario y apellido están en el empleado
                'slots' => []
            ];

            while ($puntero->lte($finDia)) {
                $horaString = $puntero->format('H:i');

                // saltar slots pasados
                if ($isToday && Carbon::parse("$fecha $horaString")->lt($now)) {
                    $puntero->addMinutes($duracion);
                    continue;
                }
                // saltar break
                if ($puntero->between(
                    Carbon::parse("$fecha {$breakStart->format('H:i')}"),
                    Carbon::parse("$fecha {$breakEnd->format('H:i')}"),
                    false
                )) {
                    $puntero = Carbon::parse("$fecha {$breakEnd->format('H:i')}");
                    continue;
                }

                // comprobar ocupación
                $ocupadas = $ocupadoPorEmpleado[$emp->id] ?? [];
                $slotInicio = $puntero->copy();
                $slotFin    = $puntero->copy()->addMinutes($duracion);
                $estaLibre = true;
                foreach ($ocupadas as $horaO) {
                    $inicioO = Carbon::parse("$fecha $horaO");
                    $finO    = $inicioO->copy()->addMinutes($duracion);
                    if ($slotInicio->lt($finO) && $slotFin->gt($inicioO)) {
                        $estaLibre = false;
                        break;
                    }
                }
                if ($estaLibre) {
                    $slotsPorEmpleado[$emp->id]['slots'][] = $horaString;
                }

                $puntero->addMinutes($duracion);
            }
        }

        return response()->json([
            'employees' => $slotsPorEmpleado
        ], 200);
    }
}
