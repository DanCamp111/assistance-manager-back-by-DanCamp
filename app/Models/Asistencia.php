<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asistencia extends Model
{
    use HasFactory;

    protected $table = 'asistencias';

    protected $fillable = [
        'usuario_id',
        'tipo_registro',
        'fecha_registro',
        'hora_exacta',
        'foto_registro'
    ];

    protected $casts = [
        'fecha_registro' => 'date:Y-m-d',
        'hora_exacta' => 'datetime:H:i:s'
    ];

    // Relación con usuario
    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }
}