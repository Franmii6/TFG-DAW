<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Notifications\Notifiable;

class Servicio extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nombre',
        'descripcion',
        'duracion',
        'precio',
    ];
 /**
  * The citas that belong to the Servicio
  *
  * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
  */
 public function citas(): BelongsToMany
 {
     return $this->belongsToMany(Cita::class, 'cita_servicios', 'servicio_id', 'cita_id');
 }
 
}
