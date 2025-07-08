<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carrera extends Model
{
    use HasFactory;

    protected $table = 'carreras';

    protected $fillable = [
        'nombre',
        'codigo',
        'descripcion'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}