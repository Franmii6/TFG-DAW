<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class Contrato extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'cliente_id',
        'numero_de_atenciones',
        'numero_de_atenciones_realizadas',
        'fecha_inicio',
        'fecha_fin',
    ];

    /**
     * Get the cliente that owns the Contrato
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }
    /**
     * Get all of the citas for the Contrato
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class);
    }
}
