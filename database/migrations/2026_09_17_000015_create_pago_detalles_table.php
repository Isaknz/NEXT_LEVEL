<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pago_detalles')) {
            return;
        }

        Schema::create('pago_detalles', function (Blueprint $table) {
            $table->id('id_pago_detalle');
            $table->unsignedBigInteger('id_pago');
            $table->unsignedBigInteger('id_cuenta');
            $table->decimal('monto_aplicado', 12, 2);
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['id_pago', 'id_cuenta'], 'uq_pago_detalle_pago_cuenta');
            $table->index('id_cuenta', 'idx_pago_detalles_cuenta');

            $table->foreign('id_pago', 'fk_pago_detalles_pago')
                  ->references('id_pago')
                  ->on('pagos')
                  ->onUpdate('cascade');
            $table->foreign('id_cuenta', 'fk_pago_detalles_cuenta')
                  ->references('id_cuenta')
                  ->on('cuentas_por_cobrar')
                  ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pago_detalles');
    }
};