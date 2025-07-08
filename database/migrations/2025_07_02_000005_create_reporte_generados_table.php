<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('reportes_generados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supervisor_id')->constrained('usuarios');
            $table->enum('tipo_reporte', ['asistencias', 'incidencias', 'combinado']);
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->enum('formato', ['pdf', 'excel']);
            $table->string('nombre_archivo', 255);
            $table->string('ruta_archivo', 255);
            $table->json('parametros')->nullable();
            $table->timestamps();
            
            // Índice para búsquedas por fechas
            $table->index(['fecha_inicio', 'fecha_fin']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('reportes_generados');
    }
};