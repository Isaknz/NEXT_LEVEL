<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('comprobantes_pago')) {
            return;
        }

        Schema::create('comprobantes_pago', function (Blueprint $table) {
            $table->id('id_comprobante_pago');
            $table->unsignedBigInteger('id_pago');
            $table->enum('tipo', ['RECIBO_INTERNO', 'BOLETA', 'FACTURA', 'NOTA_CREDITO', 'OTRO'])->default('RECIBO_INTERNO');
            $table->string('serie', 10)->nullable();
            $table->string('numero', 30)->nullable();
            $table->dateTime('fecha_emision')->useCurrent();
            $table->string('archivo_url', 500)->nullable();
            $table->timestamps();

            $table->unique(['serie', 'numero'], 'uq_comprobante_pago_serie_numero');
            $table->index('id_pago', 'idx_comprobantes_pago_pago');

            $table->foreign('id_pago', 'fk_comprobantes_pago_pago')
                  ->references('id_pago')
                  ->on('pagos')
                  ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comprobantes_pago');
    }
};