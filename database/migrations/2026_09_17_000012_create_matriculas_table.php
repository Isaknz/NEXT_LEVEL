<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('matriculas')) {
            return;
        }

        Schema::create('matriculas', function (Blueprint $table) {
            $table->id('id_matricula');
            $table->string('codigo', 40)->unique();
            $table->unsignedBigInteger('id_alumno');
            $table->unsignedBigInteger('id_periodo');
            $table->unsignedBigInteger('id_nivel');
            $table->enum('modalidad', ['ESCOLAR', 'ACADEMIA']);
            $table->unsignedBigInteger('id_grado')->nullable();
            $table->unsignedBigInteger('id_ciclo')->nullable();
            $table->date('fecha_matricula');
            $table->enum('tipo_matricula', ['NUEVO', 'REGULAR', 'TRASLADO', 'REINGRESO'])->default('NUEVO');
            $table->enum('estado', ['PENDIENTE', 'ACTIVA', 'RETIRADA', 'ANULADA', 'FINALIZADA'])->default('PENDIENTE');
            $table->string('observaciones', 500)->nullable();
            $table->unsignedBigInteger('registrado_por');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->index(['id_alumno', 'id_periodo'], 'idx_matriculas_alumno_periodo');
            $table->index(['id_periodo', 'modalidad', 'estado'], 'idx_matriculas_reporte');
            $table->index('id_ciclo', 'idx_matriculas_ciclo');

            $table->foreign('id_alumno', 'fk_matriculas_alumno')
                  ->references('id_alumno')
                  ->on('alumnos')
                  ->onUpdate('cascade');
            $table->foreign('id_periodo', 'fk_matriculas_periodo')
                  ->references('id_periodo')
                  ->on('periodos_academicos')
                  ->onUpdate('cascade');
            $table->foreign('id_nivel', 'fk_matriculas_nivel')
                  ->references('id_nivel')
                  ->on('niveles')
                  ->onUpdate('cascade');
            $table->foreign(['id_grado', 'id_nivel'], 'fk_matriculas_grado_nivel')
                  ->references(['id_grado', 'id_nivel'])
                  ->on('grados');
            $table->foreign(['id_ciclo', 'id_periodo'], 'fk_matriculas_ciclo_periodo')
                  ->references(['id_ciclo', 'id_periodo'])
                  ->on('ciclos_academia');
            $table->foreign('registrado_por', 'fk_matriculas_registrado_por')
                  ->references('id')
                  ->on('users')
                  ->onUpdate('cascade');
            $table->foreign('updated_by', 'fk_matriculas_updated_by')
                  ->references('id')
                  ->on('users')
                  ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matriculas');
    }
};