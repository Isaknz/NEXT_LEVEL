<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('periodos_academicos')) {
            return;
        }

        Schema::create('periodos_academicos', function (Blueprint $table) {
            $table->id('id_periodo');
            $table->string('codigo', 30)->unique();
            $table->string('nombre', 100);
            $table->unsignedSmallInteger('anio');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->enum('estado', ['PLANIFICADO', 'ABIERTO', 'CERRADO'])->default('PLANIFICADO');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periodos_academicos');
    }
};