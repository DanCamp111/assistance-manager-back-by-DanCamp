<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('carreras', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('codigo', 20)->unique()->nullable();
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('carreras');
    }
};