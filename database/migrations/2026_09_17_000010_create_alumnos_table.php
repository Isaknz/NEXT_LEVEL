<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('alumnos')) {
            return;
        }

        Schema::create('alumnos', function (Blueprint $table) {
            $table->id('id_alumno');
            $table->string('codigo', 30)->nullable()->unique();
            $table->char('dni', 8)->nullable();
            $table->string('nombres', 100);
            $table->string('apellidos', 100);
            $table->date('fecha_nacimiento')->nullable();
            $table->enum('sexo', ['F', 'M', 'OTRO', 'NO_DECLARA'])->nullable();
            $table->string('celular', 20)->nullable();
            $table->string('email', 120)->nullable();
            $table->string('direccion', 255)->nullable();
            $table->unsignedBigInteger('id_grado')->nullable();
            $table->unsignedBigInteger('id_apoderado')->nullable();
            $table->string('parentesco', 20)->nullable();
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['apellidos', 'nombres'], 'idx_alumnos_busqueda');
            $table->index('estado', 'idx_alumnos_estado');
            $table->index('id_grado', 'idx_alumnos_grado');
            $table->index('id_apoderado', 'idx_alumnos_apoderado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumnos');
    }
};