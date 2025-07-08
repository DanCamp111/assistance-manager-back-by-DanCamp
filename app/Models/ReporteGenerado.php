<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReporteGenerado extends Model
{
    use HasFactory;

    protected $table = 'reportes_generados';

    protected $fillable = [
        'supervisor_id',
        'tipo_reporte',
        'fecha_inicio',
        'fecha_fin',
        'formato',
        'nombre_archivo',
        'ruta_archivo',
        'parametros'
    ];

    protected $casts = [
        'parametros' => 'array',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date'
    ];

    // Relación con el supervisor que generó el reporte
    public function supervisor()
    {
        return $this->belongsTo(Usuario::class, 'supervisor_id');
    }
}