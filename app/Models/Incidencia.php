<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Incidencia extends Model
{
    use HasFactory;

    const ESTATUS_PENDIENTE = 'pendiente';
    const ESTATUS_APROBADO = 'aprobado';
    const ESTATUS_RECHAZADO = 'rechazado';

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
        'observaciones',
        'fecha_solicitud',
        'fecha_revision',
    ];

    protected $casts = [
        'fecha_ausencia' => 'date',
        'hora_salida' => 'datetime:H:i:s',
        'hora_regreso' => 'datetime:H:i:s',
        'hora_transporte' => 'float',
        'fecha_solicitud' => 'datetime',
        'fecha_revision' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function supervisor()
    {
        return $this->belongsTo(Usuario::class, 'supervisor_id');
    }
}
