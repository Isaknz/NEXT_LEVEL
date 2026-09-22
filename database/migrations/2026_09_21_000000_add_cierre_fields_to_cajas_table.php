<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cajas', function (Blueprint $table) {
            $table->decimal('saldo_inicial', 12, 2)->nullable()->after('estado');
            $table->decimal('monto_apertura', 12, 2)->nullable()->after('saldo_inicial');
            $table->decimal('monto_cierre', 12, 2)->nullable()->after('monto_apertura');
            $table->timestamp('fecha_cierre')->nullable()->after('monto_cierre');
            $table->unsignedBigInteger('usuarios_id_cerrado')->nullable()->after('fecha_cierre');
        });

        DB::statement("ALTER TABLE cajas MODIFY estado ENUM('ACTIVA','INACTIVA','CERRADA') DEFAULT 'ACTIVA'");

        Schema::table('cajas', function (Blueprint $table) {
            $table->foreign('usuarios_id_cerrado')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cajas', function (Blueprint $table) {
            $table->dropForeign(['usuarios_id_cerrado']);
            $table->dropColumn(['saldo_inicial', 'monto_apertura', 'monto_cierre', 'fecha_cierre', 'usuarios_id_cerrado']);
        });

        DB::statement("ALTER TABLE cajas MODIFY estado ENUM('ACTIVA','INACTIVA') DEFAULT 'ACTIVA'");
    }
};
