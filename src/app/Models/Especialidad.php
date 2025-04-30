<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Notifications\Notifiable;

class Especialidad extends Model
{
    protected $fillable = [
        'nombre',
    ];
    use HasFactory, Notifiable;
    /**
     * Get the empleado that owns the Especialidad
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }
    /**
     * The empleados that belong to the Especialidad
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function empleados(): BelongsToMany
    {
        return $this->belongsToMany(Empleado::class, 'empleado_especialidads', 'especialidad_id', 'empleado_id');
    }
}
