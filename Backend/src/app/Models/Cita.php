<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class Cita extends Model
{

    use HasFactory, Notifiable;

    protected $fillable = [
        'cliente_id',
        'empleado_id',
        'contrato_id',
        'fecha',
        'estado',
        'numero_de_atenciones',
        'has_comment', // <--- Añadido aquí
    ];

    // ← Aquí añadimos el cast de fecha para que sea Carbon
    protected $casts = [
        'fecha' => 'datetime',
        'has_comment' => 'boolean', // <--- Añadido aquí
    ];

    /**
     * Get the cliente that owns the Cita
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }
    /**
     * Get the contrato that owns the Cita
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contrato(): BelongsTo
    {
        return $this->belongsTo(Contrato::class);
    }
    /**
     * Get the empleado that owns the Cita
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }
    /**
     * The servicios that belong to the Cita
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function servicios(): BelongsToMany
    {
        return $this->belongsToMany(Servicio::class, 'cita_servicios', 'cita_id', 'servicio_id');
    }

    /**
     * Get the comentario associated with the Cita
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function comentario()
    {
        return $this->hasOne(Comentario::class, 'cita_id');
    }
}
