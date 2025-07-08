<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('incidencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios');
            $table->string('tipo_incidencia', 50);
            $table->text('motivo');
            $table->date('fecha_ausencia');
            $table->time('hora_salida')->nullable();
            $table->time('hora_regreso')->nullable();
            $table->time('hora_transporte')->nullable();
            $table->string('documento_justificativo')->nullable();
            $table->enum('estatus', ['pendiente', 'aprobado', 'rechazado'])->default('pendiente');
            $table->foreignId('supervisor_id')->nullable()->constrained('usuarios');
            $table->text('observaciones')->nullable();
            $table->timestamp('fecha_solicitud')->useCurrent();
            $table->timestamps();
            
            // Índices para mejorar búsquedas
            $table->index('fecha_ausencia');
            $table->index('estatus');
        });
    }

    public function down()
    {
        Schema::dropIfExists('incidencias');
    }
};