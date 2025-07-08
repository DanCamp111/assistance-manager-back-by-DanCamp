<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'usuarios';

    protected $fillable = [
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'correo',
        'password',
        'rol_id',
        'carrera_id',
        'status'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Relaciones
    public function rol()
    {
        return $this->belongsTo(Rol::class);
    }

    public function carrera()
    {
        return $this->belongsTo(Carrera::class);
    }

    public function esAdmin()
    {
        return $this->rol_id == 1; // Ajusta según tu estructura de roles
    }
}
