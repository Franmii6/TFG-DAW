<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Usuario extends Authenticatable
{
   /** @use HasFactory<\Database\Factories\UserFactory> */
   use HasFactory, Notifiable, HasApiTokens;

   /**
    * The attributes that are mass assignable.
    *
    * @var list<string>
    */
    protected $fillable = [
        'nombre',
        'nombreUsuario',
        'email',
        'contrasena',
    ];

   /**
    * The attributes that should be hidden for serialization.
    *
    * @var list<string>
    */
   protected $hidden = [
       'contrasena',
   ];

   /**
     * Laravel llama a este método para obtener el hash
     * con el que comparar la contraseña en login.
     */
    public function getAuthPassword()
    {
        return $this->contrasena;
    }

   /**
    * Get the empleado associated with the Usuario
    *
    * @return \Illuminate\Database\Eloquent\Relations\HasOne
    */
   public function empleado(): HasOne
   {
       return $this->hasOne(Empleado::class);
   }

   /**
    * Get the cliente associated with the Usuario
    *
    * @return \Illuminate\Database\Eloquent\Relations\HasOne
    */
   public function cliente(): HasOne
   {
       return $this->hasOne(Cliente::class, 'usuario_id');
   }

   /**
    * Get the attributes that should be cast.
    *
    * @return array<string, string>
    */
   protected function casts(): array
   {
        return [
           'email_verified_at' => 'datetime',
           'password' => 'hashed',
        ];
   }
}
