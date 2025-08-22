<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('horarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->onDelete('cascade');
            $table->enum('dia_semana', ['lunes', 'martes', 'miércoles', 'jueves', 'viernes']);
            $table->time('hora_entrada');
            $table->time('hora_salida');
            $table->time('hora_comida_inicio')->nullable();
            $table->time('hora_comida_fin')->nullable();
            $table->timestamps();

            $table->unique(['usuario_id', 'dia_semana'], 'horario_unico_usuario_dia');
        });
    }

    public function down(): void {
        Schema::dropIfExists('horarios');
    }
};
