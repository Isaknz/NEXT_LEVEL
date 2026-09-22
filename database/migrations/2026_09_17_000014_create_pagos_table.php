<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pagos')) {
            return;
        }

        Schema::create('pagos', function (Blueprint $table) {
            $table->id('id_pago');
            $table->string('codigo', 40)->unique();
            $table->unsignedBigInteger('id_matricula');
            $table->unsignedBigInteger('id_caja');
            $table->dateTime('fecha_pago');
            $table->enum('metodo_pago', ['EFECTIVO', 'YAPE', 'PLIN', 'TRANSFERENCIA', 'TARJETA', 'OTRO']);
            $table->string('numero_operacion', 100)->nullable();
            $table->decimal('monto_total', 12, 2);
            $table->enum('estado', ['CONFIRMADO', 'ANULADO'])->default('CONFIRMADO');
            $table->string('observaciones', 500)->nullable();
            $table->unsignedBigInteger('registrado_por');
            $table->unsignedBigInteger('anulado_por')->nullable();
            $table->dateTime('anulado_at')->nullable();
            $table->string('motivo_anulacion', 255)->nullable();
            $table->timestamps();

            $table->index(['id_matricula', 'fecha_pago'], 'idx_pagos_matricula_fecha');
            $table->index(['estado', 'fecha_pago', 'id_caja'], 'idx_pagos_reporte');

            $table->foreign('id_matricula', 'fk_pagos_matricula')
                  ->references('id_matricula')
                  ->on('matriculas')
                  ->onUpdate('cascade');
            $table->foreign('id_caja', 'fk_pagos_caja')
                  ->references('id_caja')
                  ->on('cajas')
                  ->onUpdate('cascade');
            $table->foreign('registrado_por', 'fk_pagos_registrado_por')
                  ->references('id')
                  ->on('users')
                  ->onUpdate('cascade');
            $table->foreign('anulado_por', 'fk_pagos_anulado_por')
                  ->references('id')
                  ->on('users')
                  ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};