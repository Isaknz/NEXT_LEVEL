<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `matriculas` CHANGE `estado` `estado` ENUM('PENDIENTE','ACTIVA','RETIRADA','ANULADA','FINALIZADA','PAGADA') DEFAULT 'PENDIENTE' NOT NULL");
        } elseif ($driver === 'sqlite') {
            Schema::dropIfExists('pagos');
            Schema::dropIfExists('cuentas_por_cobrar');
            Schema::dropIfExists('matriculas');

            Schema::create('matriculas', function (Blueprint $table) {
                $table->id('id_matricula');
                $table->string('codigo', 40)->unique();
                $table->unsignedBigInteger('id_alumno');
                $table->unsignedBigInteger('id_periodo');
                $table->unsignedBigInteger('id_nivel');
                $table->enum('modalidad', ['ESCOLAR', 'ACADEMIA']);
                $table->unsignedBigInteger('id_grado')->nullable();
                $table->unsignedBigInteger('id_ciclo')->nullable();
                $table->date('fecha_matricula');
                $table->enum('tipo_matricula', ['NUEVO', 'REGULAR', 'TRASLADO', 'REINGRESO'])->default('NUEVO');
                $table->enum('estado', ['PENDIENTE', 'ACTIVA', 'RETIRADA', 'ANULADA', 'FINALIZADA', 'PAGADA'])->default('PENDIENTE');
                $table->string('observaciones', 500)->nullable();
                $table->unsignedBigInteger('registrado_por');
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();

                $table->index(['id_alumno', 'id_periodo'], 'idx_matriculas_alumno_periodo');
                $table->index(['id_periodo', 'modalidad', 'estado'], 'idx_matriculas_reporte');
                $table->index('id_ciclo', 'idx_matriculas_ciclo');

                $table->foreign('id_alumno', 'fk_matriculas_alumno')
                      ->references('id_alumno')
                      ->on('alumnos')
                      ->onUpdate('cascade');
                $table->foreign('id_periodo', 'fk_matriculas_periodo')
                      ->references('id_periodo')
                      ->on('periodos_academicos')
                      ->onUpdate('cascade');
                $table->foreign('id_nivel', 'fk_matriculas_nivel')
                      ->references('id_nivel')
                      ->on('niveles')
                      ->onUpdate('cascade');
                $table->foreign('id_grado', 'fk_matriculas_grado_nivel')
                      ->references('id_grado')
                      ->on('grados')
                      ->onUpdate('cascade');
                $table->foreign('registrado_por', 'fk_matriculas_registrado_por')
                      ->references('id')
                      ->on('users')
                      ->onUpdate('cascade');
                $table->foreign('updated_by', 'fk_matriculas_updated_by')
                      ->references('id')
                      ->on('users')
                      ->onUpdate('cascade');
            });

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

            Schema::create('pagos', function (Blueprint $table) {
                $table->id('id_pago');
                $table->string('codigo', 40)->unique();
                $table->unsignedBigInteger('id_matricula');
                $table->unsignedBigInteger('id_caja');
                $table->dateTime('fecha_pago');
                $table->enum('metodo_pago', ['EFECTIVO', 'YAPE', 'PLIN', 'TARJETA', 'OTRO']);
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
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `matriculas` CHANGE `estado` `estado` ENUM('PENDIENTE','ACTIVA','RETIRADA','ANULADA','FINALIZADA') DEFAULT 'PENDIENTE' NOT NULL");
        } elseif ($driver === 'sqlite') {
            Schema::dropIfExists('pagos');
            Schema::dropIfExists('cuentas_por_cobrar');
            Schema::dropIfExists('matriculas');

            Schema::create('matriculas', function (Blueprint $table) {
                $table->id('id_matricula');
                $table->string('codigo', 40)->unique();
                $table->unsignedBigInteger('id_alumno');
                $table->unsignedBigInteger('id_periodo');
                $table->unsignedBigInteger('id_nivel');
                $table->enum('modalidad', ['ESCOLAR', 'ACADEMIA']);
                $table->unsignedBigInteger('id_grado')->nullable();
                $table->unsignedBigInteger('id_ciclo')->nullable();
                $table->date('fecha_matricula');
                $table->enum('tipo_matricula', ['NUEVO', 'REGULAR', 'TRASLADO', 'REINGRESO'])->default('NUEVO');
                $table->enum('estado', ['PENDIENTE', 'ACTIVA', 'RETIRADA', 'ANULADA', 'FINALIZADA'])->default('PENDIENTE');
                $table->string('observaciones', 500)->nullable();
                $table->unsignedBigInteger('registrado_por');
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();

                $table->index(['id_alumno', 'id_periodo'], 'idx_matriculas_alumno_periodo');
                $table->index(['id_periodo', 'modalidad', 'estado'], 'idx_matriculas_reporte');
                $table->index('id_ciclo', 'idx_matriculas_ciclo');

                $table->foreign('id_alumno', 'fk_matriculas_alumno')
                      ->references('id_alumno')
                      ->on('alumnos')
                      ->onUpdate('cascade');
                $table->foreign('id_periodo', 'fk_matriculas_periodo')
                      ->references('id_periodo')
                      ->on('periodos_academicos')
                      ->onUpdate('cascade');
                $table->foreign('id_nivel', 'fk_matriculas_nivel')
                      ->references('id_nivel')
                      ->on('niveles')
                      ->onUpdate('cascade');
                $table->foreign('id_grado', 'fk_matriculas_grado_nivel')
                      ->references('id_grado')
                      ->on('grados')
                      ->onUpdate('cascade');
                $table->foreign('registrado_por', 'fk_matriculas_registrado_por')
                      ->references('id')
                      ->on('users')
                      ->onUpdate('cascade');
                $table->foreign('updated_by', 'fk_matriculas_updated_by')
                      ->references('id')
                      ->on('users')
                      ->onUpdate('cascade');
            });

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

            Schema::create('pagos', function (Blueprint $table) {
                $table->id('id_pago');
                $table->string('codigo', 40)->unique();
                $table->unsignedBigInteger('id_matricula');
                $table->unsignedBigInteger('id_caja');
                $table->dateTime('fecha_pago');
                $table->enum('metodo_pago', ['EFECTIVO', 'YAPE', 'PLIN', 'TARJETA', 'OTRO']);
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
    }
};
