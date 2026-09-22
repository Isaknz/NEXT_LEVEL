<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cuentas_por_cobrar')) {
            return;
        }

        Schema::create('cuentas_por_cobrar', function (Blueprint $table) {
            $table->id('id_cuenta');
            $table->unsignedBigInteger('id_matricula');
            $table->unsignedBigInteger('id_concepto');
            $table->string('referencia', 80);
            $table->string('descripcion', 255)->nullable();
            $table->date('fecha_emision');
            $table->date('fecha_vencimiento')->nullable();
            $table->decimal('monto_original', 12, 2);
            $table->decimal('descuento', 12, 2)->default(0);
            $table->decimal('recargo', 12, 2)->default(0);
            $table->enum('estado', ['PENDIENTE', 'PARCIAL', 'PAGADA', 'ANULADA'])->default('PENDIENTE');
            $table->dateTime('anulado_at')->nullable();
            $table->string('motivo_anulacion', 255)->nullable();
            $table->unsignedBigInteger('creado_por');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['id_matricula', 'id_concepto', 'referencia'], 'uq_cxc_matricula_concepto_referencia');
            $table->index(['estado', 'fecha_vencimiento'], 'idx_cxc_estado_vencimiento');
            $table->index('id_matricula', 'idx_cxc_matricula');

            $table->foreign('id_matricula', 'fk_cxc_matricula')
                  ->references('id_matricula')
                  ->on('matriculas')
                  ->onUpdate('cascade');
            $table->foreign('id_concepto', 'fk_cxc_concepto')
                  ->references('id_concepto')
                  ->on('conceptos_cobro')
                  ->onUpdate('cascade');
            $table->foreign('creado_por', 'fk_cxc_creado_por')
                  ->references('id')
                  ->on('users')
                  ->onUpdate('cascade');
            $table->foreign('updated_by', 'fk_cxc_updated_by')
                  ->references('id')
                  ->on('users')
                  ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuentas_por_cobrar');
    }
};