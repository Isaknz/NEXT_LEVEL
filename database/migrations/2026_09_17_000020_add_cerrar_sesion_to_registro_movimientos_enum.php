<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE registro_movimientos MODIFY accion ENUM('CREAR','ACTUALIZAR','ANULAR','ELIMINAR','VER','EXPORTAR','IMPRIMIR','INICIAR_SESION','CERRAR_SESION') NOT NULL");
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE registro_movimientos MODIFY accion ENUM('CREAR','ACTUALIZAR','ANULAR','ELIMINAR','VER','EXPORTAR','IMPRIMIR','INICIAR_SESION') NOT NULL");
    }
};