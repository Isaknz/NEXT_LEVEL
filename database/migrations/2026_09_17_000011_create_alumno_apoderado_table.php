<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('alumno_apoderado')) {
            return;
        }

        Schema::create('alumno_apoderado', function (Blueprint $table) {
            $table->unsignedBigInteger('id_alumno');
            $table->unsignedBigInteger('id_apoderado');
            $table->enum('parentesco', ['MADRE', 'PADRE', 'TUTOR', 'ABUELO_A', 'HERMANO_A', 'TIO_A', 'OTRO'])->default('TUTOR');
            $table->boolean('es_principal')->default(false);
            $table->boolean('autorizado_recojo')->default(false);
            $table->timestamps();

            $table->primary(['id_alumno', 'id_apoderado']);
            $table->index(['id_alumno', 'es_principal'], 'idx_alumno_apoderado_principal');
            $table->index('id_apoderado', 'fk_alumno_apoderado_apoderado');

            $table->foreign('id_alumno', 'fk_alumno_apoderado_alumno')
                  ->references('id_alumno')
                  ->on('alumnos')
                  ->onUpdate('cascade');
            $table->foreign('id_apoderado', 'fk_alumno_apoderado_apoderado')
                  ->references('id_apoderado')
                  ->on('apoderados')
                  ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumno_apoderado');
    }
};