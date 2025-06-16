<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Cliente;
use App\Models\Cita;

class Comentario extends Model
{
    use HasFactory;

    // Especifica la tabla en la base de datos asociada a este modelo.
    protected $table = 'comentarios';

    // Campos que se pueden asignar masivamente (mass assignment).
    protected $fillable = [
        'cliente_id',   // ID del cliente que hizo la cita y el comentario
        'cita_id',      // ID de la cita a la que corresponde este comentario
        'texto',        // Texto del comentario/opinión
        'valoracion',   // Valoración numérica (1–5) o null si no se usa
    ];

    /**
     * Relación con el modelo Cliente.
     * Un Comentario pertenece a un Cliente.
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * Relación con el modelo Cita.
     * Un Comentario pertenece a una Cita.
     */
    public function cita(): BelongsTo
    {
        return $this->belongsTo(Cita::class);
    }
}
