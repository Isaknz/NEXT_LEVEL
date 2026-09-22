<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('apoderados')) {
            return;
        }

        Schema::create('apoderados', function (Blueprint $table) {
            $table->id('id_apoderado');
            $table->char('dni', 8)->nullable()->unique();
            $table->string('nombres', 100);
            $table->string('apellidos', 100);
            $table->string('celular', 20)->nullable();
            $table->string('email', 120)->nullable();
            $table->string('direccion', 255)->nullable();
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['apellidos', 'nombres'], 'idx_apoderados_busqueda');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apoderados');
    }
};