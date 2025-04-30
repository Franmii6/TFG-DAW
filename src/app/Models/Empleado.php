<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;

class Empleado extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'usuario_id',
        'apellidos',
        'tlf',
        'direccion',
        'municipio',
        'provincia',
        'anos_experiencia', 
        'DNI'
    ];
    /**
     * Get the user associated with the Cliente
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    
        /**
     * The empleados that belong to the Especialidad
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function especialidades(): BelongsToMany
    {
        return $this->belongsToMany(Especialidad::class, 'empleado_especilidads', 'empleado_id', 'especialidad_id');
    }
}
