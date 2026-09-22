<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ciclos_academia')) {
            return;
        }

        Schema::create('ciclos_academia', function (Blueprint $table) {
            $table->id('id_ciclo');
            $table->unsignedBigInteger('id_periodo');
            $table->unsignedBigInteger('id_facultad');
            $table->string('nombre', 120);
            $table->enum('turno', ['MANANA', 'TARDE', 'NOCHE', 'COMPLETO'])->default('COMPLETO');
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->decimal('monto_referencial', 12, 2)->default(0);
            $table->unsignedSmallInteger('vacantes')->nullable();
            $table->enum('estado', ['PLANIFICADO', 'ABIERTO', 'CERRADO', 'CANCELADO'])->default('PLANIFICADO');
            $table->timestamps();

            $table->unique(['id_periodo', 'id_facultad', 'nombre', 'turno'], 'uq_ciclo_periodo_facultad_nombre_turno');
            $table->unique(['id_ciclo', 'id_periodo'], 'uq_ciclos_id_periodo');
            $table->index('id_facultad', 'idx_ciclos_facultad');

            $table->foreign('id_facultad', 'fk_ciclos_facultad')
                  ->references('id_facultad')
                  ->on('facultades')
                  ->onUpdate('cascade');
            $table->foreign('id_periodo', 'fk_ciclos_periodo')
                  ->references('id_periodo')
                  ->on('periodos_academicos')
                  ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ciclos_academia');
    }
};