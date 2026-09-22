<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('gastos')) {
            return;
        }

        Schema::create('gastos', function (Blueprint $table) {
            $table->id('id_gasto');
            $table->string('codigo', 40)->unique();
            $table->unsignedBigInteger('id_categoria_gasto');
            $table->unsignedBigInteger('id_caja');
            $table->dateTime('fecha_gasto');
            $table->string('proveedor', 150)->nullable();
            $table->string('concepto', 150);
            $table->string('descripcion', 500)->nullable();
            $table->decimal('monto', 12, 2);
            $table->enum('tipo_comprobante', ['BOLETA', 'FACTURA', 'RECIBO', 'NOTA', 'SIN_COMPROBANTE'])->default('SIN_COMPROBANTE');
            $table->string('serie_comprobante', 10)->nullable();
            $table->string('numero_comprobante', 50)->nullable();
            $table->string('archivo_url', 500)->nullable();
            $table->enum('estado', ['REGISTRADO', 'ANULADO'])->default('REGISTRADO');
            $table->unsignedBigInteger('registrado_por');
            $table->unsignedBigInteger('anulado_por')->nullable();
            $table->dateTime('anulado_at')->nullable();
            $table->string('motivo_anulacion', 255)->nullable();
            $table->timestamps();

            $table->index(['fecha_gasto', 'estado', 'id_caja'], 'idx_gastos_reporte');
            $table->index('id_categoria_gasto', 'idx_gastos_categoria');

            $table->foreign('id_categoria_gasto', 'fk_gastos_categoria')
                  ->references('id_categoria_gasto')
                  ->on('categorias_gasto')
                  ->onUpdate('cascade');
            $table->foreign('id_caja', 'fk_gastos_caja')
                  ->references('id_caja')
                  ->on('cajas')
                  ->onUpdate('cascade');
            $table->foreign('registrado_por', 'fk_gastos_registrado_por')
                  ->references('id')
                  ->on('users')
                  ->onUpdate('cascade');
            $table->foreign('anulado_por', 'fk_gastos_anulado_por')
                  ->references('id')
                  ->on('users')
                  ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gastos');
    }
};