<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    use HasFactory;

    protected $table = 'horarios';

    protected $fillable = [
        'usuario_id',
        'dia_semana',
        'hora_entrada',
        'hora_salida',
        'hora_comida_inicio',
        'hora_comida_fin'
    ];

    protected $casts = [
        'hora_entrada' => 'datetime:H:i:s',
        'hora_salida' => 'datetime:H:i:s',
        'hora_comida_inicio' => 'datetime:H:i:s',
        'hora_comida_fin' => 'datetime:H:i:s',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }
}
