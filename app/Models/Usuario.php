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

    public function horarios()
    {
        return $this->hasMany(Horario::class);
    }

    // Añade estas relaciones que faltaban
    public function asistencias()
    {
        return $this->hasMany(Asistencia::class);
    }

    public function incidencias()
    {
        return $this->hasMany(Incidencia::class);
    }

    public function esAdmin()
    {
        return $this->rol_id == 1;
    }

    // Método para obtener el nombre completo
    public function getNombreCompletoAttribute()
    {
        return trim("{$this->nombre} {$this->apellido_paterno} {$this->apellido_materno}");
    }
}