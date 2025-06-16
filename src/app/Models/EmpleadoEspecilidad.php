<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class EmpleadoEspecialidad extends Pivot
{
    // O si la has renombrado a empleado_especialidad:
    protected $table = 'empleado_especialidad';

    protected $fillable = [
        'empleado_id',
        'especialidad_id',
    ];
}
