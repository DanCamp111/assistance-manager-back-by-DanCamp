<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50);
            $table->string('apellido_paterno', 50);
            $table->string('apellido_materno', 50);
            $table->string('correo')->unique();
            $table->string('password');
            $table->foreignId('rol_id')->constrained('roles');
            $table->foreignId('carrera_id')->nullable()->constrained('carreras');
            $table->enum('status', ['activo', 'inactivo'])->default('activo');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('usuarios');
    }
};