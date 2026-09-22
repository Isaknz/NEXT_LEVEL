<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('grados')) {
            return;
        }

        Schema::create('grados', function (Blueprint $table) {
            $table->id('id_grado');
            $table->unsignedBigInteger('id_nivel');
            $table->string('nombre', 30);
            $table->unsignedTinyInteger('orden');
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->timestamps();

            $table->unique(['id_nivel', 'nombre'], 'uq_grados_nivel_nombre');
            $table->unique(['id_grado', 'id_nivel'], 'uq_grados_id_nivel');
            $table->unique(['id_nivel', 'orden'], 'uq_grados_nivel_orden');

            $table->foreign('id_nivel', 'fk_grados_nivel')
                  ->references('id_nivel')
                  ->on('niveles')
                  ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grados');
    }
};