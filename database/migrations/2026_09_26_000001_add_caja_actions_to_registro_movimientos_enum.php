<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * El enum original de registro_movimientos.accion solo contemplaba
 * CREAR, ACTUALIZAR, ANULAR, ELIMINAR, VER, EXPORTAR, IMPRIMIR,
 * INICIAR_SESION y CERRAR_SESION.
 *
 * El cierre, la reapertura y el ajuste manual de caja son eventos de negocio
 * distintos de una simple actualización, así que se registran con su propia
 * acción en lugar de diluirse dentro de ACTUALIZAR.
 *
 * MODIFY preserva las filas existentes; ADD VALUE no está soportado en MySQL.
 */
return new class extends Migration
{
    private const ENUM_COMPLETO = "'CREAR','ACTUALIZAR','ANULAR','ELIMINAR','VER','EXPORTAR','IMPRIMIR','INICIAR_SESION','CERRAR_SESION','CERRAR_CAJA','REABRIR_CAJA','AJUSTE_CAJA'";

    private const ENUM_ANTERIOR = "'CREAR','ACTUALIZAR','ANULAR','ELIMINAR','VER','EXPORTAR','IMPRIMIR','INICIAR_SESION','CERRAR_SESION'";

    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE registro_movimientos MODIFY accion ENUM(' . self::ENUM_COMPLETO . ') NOT NULL');
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        // Solo se revierte si nadie ha registrado todavía una acción de caja.
        $usos = DB::table('registro_movimientos')
            ->whereIn('accion', ['CERRAR_CAJA', 'REABRIR_CAJA', 'AJUSTE_CAJA'])
            ->count();

        if ($usos > 0) {
            throw new RuntimeException(
                "No se puede revertir: existen {$usos} registros de auditoría con acciones de caja. "
                . 'Elimínalos manualmente si realmente necesitas volver al enum anterior.'
            );
        }

        DB::statement('ALTER TABLE registro_movimientos MODIFY accion ENUM(' . self::ENUM_ANTERIOR . ') NOT NULL');
    }
};
