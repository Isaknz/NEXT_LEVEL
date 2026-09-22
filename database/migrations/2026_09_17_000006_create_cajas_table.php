<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cajas')) {
            return;
        }

        Schema::create('cajas', function (Blueprint $table) {
            $table->id('id_caja');
            $table->string('codigo', 30)->unique();
            $table->string('nombre', 100);
            $table->enum('tipo', ['EFECTIVO', 'BANCO', 'BILLETERA_DIGITAL']);
            $table->enum('estado', ['ACTIVA', 'INACTIVA'])->default('ACTIVA');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cajas');
    }
};