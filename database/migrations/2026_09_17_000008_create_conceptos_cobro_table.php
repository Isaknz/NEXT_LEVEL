<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('conceptos_cobro')) {
            return;
        }

        Schema::create('conceptos_cobro', function (Blueprint $table) {
            $table->id('id_concepto');
            $table->string('codigo', 40)->unique();
            $table->string('nombre', 120);
            $table->enum('tipo', ['MATRICULA', 'MENSUALIDAD', 'CICLO', 'MATERIAL', 'EXAMEN', 'OTRO']);
            $table->enum('modalidad_aplicable', ['ESCOLAR', 'ACADEMIA', 'AMBOS'])->default('AMBOS');
            $table->decimal('monto_referencial', 12, 2)->default(0);
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conceptos_cobro');
    }
};