<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comentario;
use App\Models\Cita;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class ComentarioController extends Controller
{
    /**
     * Añade un comentario a una cita
     */
    public function add(Request $request, $citaId)  //Usamos la petición request y la id de la cita
    {
        $request->validate([ //Validamos que se ha introducido un texto y una valoración con los parámetros indicados
            'texto'      => 'required|string|max:150',
            'valoracion' => 'nullable|integer|min:1|max:5', //La valoración puede ser nula, y tiene que ser un número entre el 1 y el 5
        ]);

        try { //Usamos try para capturar el error si lo hubiese
            $cita = Cita::findOrFail($citaId); // Intentamos guardar la cita, si la encuentra
        } catch (ModelNotFoundException) { //Si no, uso ModelNotFoundException que se usa para los métodos findOrFail($id) o firstOrFail(), para lanzar el error 404 si no se encuentra y evitar hacerlo manualmente.
            return response()->json(['message' => 'Cita no encontrada'], 404); //Mensaje para el error 404
        }

        $usuario = Auth::user(); //Guardamos en una variable el usuario autenticado
        if (! $usuario || $cita->cliente->usuario_id !== $usuario->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        // **MODIFICACIÓN CLAVE:** Verificar si ya existe un comentario para esta cita
        if (Comentario::where('cita_id', $citaId)->exists()) {
            // Si ya existe, devolvemos un 409 Conflict con un mensaje claro
            return response()->json(['message' => 'Ya existe un comentario para esta cita. Use PUT para actualizarlo.'], 409); 
        }

        $comentario = Comentario::create([
            'cliente_id' => $cita->cliente_id,
            'cita_id'    => $citaId,
            'texto'      => $request->input('texto'),
            'valoracion' => $request->input('valoracion'),
        ]);

        // **NUEVO:** Actualizar el campo has_comment en la cita
        $cita->update(['has_comment' => true]);

        return response()->json($comentario, 201); // 201 Created para una nueva creación
    }

    public function update(Request $request, $citaId)
    {
        $request->validate([
            'texto'      => 'sometimes|string',
            'valoracion' => 'sometimes|integer|min:1|max:5',
        ]);

        $comentario = Comentario::where('cita_id', $citaId)->first();
        if (! $comentario) {
            return response()->json(['message' => 'Comentario no encontrado'], 404);
        }

        $usuario = Auth::user();
        if (! $usuario || $comentario->cliente->usuario_id !== $usuario->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $comentario->update($request->only(['texto', 'valoracion']));

        // No es necesario actualizar has_comment aquí, ya debería ser true
        return response()->json($comentario, 200);
    }

    public function delete($citaId)
    {
        $comentario = Comentario::where('cita_id', $citaId)->first();
        if (! $comentario) {
            return response()->json(['message' => 'Comentario no encontrado'], 404);
        }

        $usuario = Auth::user();
        if (! $usuario || $comentario->cliente->usuario_id !== $usuario->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $comentario->delete();

        $cita = Cita::find($citaId);
        if ($cita) {
            $cita->update(['has_comment' => false]);
        }

        return response()->json(['message' => 'Comentario eliminado'], 200);
    }

    public function mostrarComentarios()
    {
        // Trae los 5 comentarios más recientes, con la relación 'cliente' y 'usuario' dentro de 'cliente' cargadas
        $comentarios = Comentario::with([

        'cliente.usuario',
        'cita.servicios',
        'cita.empleado.usuario',
        ])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return response()->json($comentarios);
    }

    public function showEspecific($citaId)
    {
        try {
            $comentario = Comentario::where('cita_id', $citaId)->firstOrFail();
            return response()->json($comentario, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'No se encontró comentario para esta cita.'], 404);
        }
    }
}
