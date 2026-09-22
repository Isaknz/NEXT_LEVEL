<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('movimientos_caja')) {
            return;
        }

        Schema::create('movimientos_caja', function (Blueprint $table) {
            $table->id('id_movimiento');
            $table->unsignedBigInteger('id_caja');
            $table->enum('tipo', ['INGRESO', 'EGRESO', 'AJUSTE_INGRESO', 'AJUSTE_EGRESO']);
            $table->enum('origen', ['PAGO_ALUMNO', 'GASTO', 'AJUSTE']);
            $table->unsignedBigInteger('id_pago')->nullable();
            $table->unsignedBigInteger('id_gasto')->nullable();
            $table->dateTime('fecha_movimiento');
            $table->decimal('monto', 12, 2);
            $table->string('descripcion', 500)->nullable();
            $table->enum('estado', ['ACTIVO', 'ANULADO'])->default('ACTIVO');
            $table->unsignedBigInteger('registrado_por');
            $table->timestamps();

            $table->unique('id_pago', 'uq_movimiento_pago');
            $table->unique('id_gasto', 'uq_movimiento_gasto');
            $table->index(['id_caja', 'fecha_movimiento', 'estado'], 'idx_movimientos_caja_reporte');

            $table->foreign('id_caja', 'fk_movimientos_caja')
                  ->references('id_caja')
                  ->on('cajas');
            $table->foreign('id_pago', 'fk_movimientos_pago')
                  ->references('id_pago')
                  ->on('pagos');
            $table->foreign('id_gasto', 'fk_movimientos_gasto')
                  ->references('id_gasto')
                  ->on('gastos');
            $table->foreign('registrado_por', 'fk_movimientos_registrado_por')
                  ->references('id')
                  ->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_caja');
    }
};