<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('asistencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios');
            $table->enum('tipo_registro', ['entrada', 'salida']);
            $table->date('fecha_registro');
            $table->time('hora_exacta');
            $table->string('foto_registro')->nullable();
            $table->timestamps();
            
            // Índice compuesto para evitar registros duplicados
            $table->unique(['usuario_id', 'fecha_registro', 'tipo_registro']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('asistencias');
    }
};