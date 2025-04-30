<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;

class Cliente extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'usuario_id',
        'apellidos',
        'tlf',
        'direccion',
        'municipio',
        'provincia', 
        'DNI'
    ];
    
    /**
     * Get the usuario that owns the Cliente
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }
    /**
     * Get all of the citas for the Cliente
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class);
    }
    /**
     * Get all of the contratos for the Cliente
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contratos(): HasMany
    {
        return $this->hasMany(Contrato::class);
    }
}
