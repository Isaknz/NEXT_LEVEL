<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Usuario: agregar last_login
        if (!Schema::hasColumn('users', 'last_login')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('last_login')->nullable()->after('estado');
            });
        }

        // Alumno: agregar RETIRADO, foto, fecha_ingreso, observaciones
        if (!Schema::hasColumn('alumnos', 'foto')) {
            Schema::table('alumnos', function (Blueprint $table) {
                $table->string('foto', 255)->nullable()->after('estado');
                $table->date('fecha_ingreso')->nullable()->after('foto');
                $table->text('observaciones')->nullable()->after('fecha_ingreso');
            });
        }

        // Pagos: motivo_anulacion y anulado_por_id
        if (!Schema::hasColumn('pagos', 'motivo_anulacion')) {
            Schema::table('pagos', function (Blueprint $table) {
                $table->text('motivo_anulacion')->nullable()->after('estado');
                $table->unsignedBigInteger('anulado_por_id')->nullable()->after('motivo_anulacion');
            });
        }

        // Gastos: comprobante y motivo_anulacion
        if (!Schema::hasColumn('gastos', 'tipo_comprobante')) {
            Schema::table('gastos', function (Blueprint $table) {
                $table->string('tipo_comprobante', 50)->nullable()->after('concepto');
                $table->string('serie', 20)->nullable()->after('tipo_comprobante');
                $table->string('numero', 20)->nullable()->after('serie');
                $table->string('archivo_adjunto', 255)->nullable()->after('numero');
                $table->text('motivo_anulacion')->nullable()->after('estado');
                $table->unsignedBigInteger('anulado_por_id')->nullable()->after('motivo_anulacion');
            });
        }

        // Cajas: saldo_inicial
        if (!Schema::hasColumn('cajas', 'saldo_inicial')) {
            Schema::table('cajas', function (Blueprint $table) {
                $table->decimal('saldo_inicial', 12, 2)->default(0)->after('estado');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('last_login');
        });

        Schema::table('alumnos', function (Blueprint $table) {
            $table->dropColumn(['foto', 'fecha_ingreso', 'observaciones']);
        });

        Schema::table('pagos', function (Blueprint $table) {
            $table->dropColumn(['motivo_anulacion', 'anulado_por_id']);
        });

        Schema::table('gastos', function (Blueprint $table) {
            $table->dropColumn(['tipo_comprobante', 'serie', 'numero', 'archivo_adjunto', 'motivo_anulacion', 'anulado_por_id']);
        });

        Schema::table('cajas', function (Blueprint $table) {
            $table->dropColumn('saldo_inicial');
        });
    }
};
