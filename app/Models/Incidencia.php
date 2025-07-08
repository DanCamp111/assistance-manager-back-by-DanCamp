<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Incidencia extends Model
{
    use HasFactory;

    protected $table = 'incidencias';

    protected $fillable = [
        'usuario_id',
        'tipo_incidencia',
        'motivo',
        'fecha_ausencia',
        'hora_salida',
        'hora_regreso',
        'hora_transporte',
        'documento_justificativo',
        'estatus',
        'supervisor_id',
        'observaciones'
    ];

    protected $casts = [
        'fecha_ausencia' => 'date',
        'hora_salida' => 'datetime:H:i:s',
        'hora_regreso' => 'datetime:H:i:s',
        'hora_transporte' => 'datetime:H:i:s',
        'fecha_solicitud' => 'datetime'
    ];

    // Relación con el usuario que reporta la incidencia
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    // Relación con el supervisor que revisa la incidencia
    public function supervisor()
    {
        return $this->belongsTo(Usuario::class, 'supervisor_id');
    }
}