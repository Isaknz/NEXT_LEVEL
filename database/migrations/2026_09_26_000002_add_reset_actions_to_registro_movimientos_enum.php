<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Un restablecimiento de contraseña es un evento de seguridad relevante: si no
 * queda registrado, nadie puede demostrar cuándo una cuenta quedó accesible de
 * nuevo ni desde qué IP se pidió el enlace.
 *
 * Se agrega RESTABLECER_CLAVE y SOLICITAR_RESET (la petición del enlace) al
 * enum de registro_movimientos.accion. MODIFY preserva las filas existentes;
 * ADD VALUE no está soportado en MySQL.
 */
return new class extends Migration
{
    private const ENUM_COMPLETO = "'CREAR','ACTUALIZAR','ANULAR','ELIMINAR','VER','EXPORTAR','IMPRIMIR','INICIAR_SESION','CERRAR_SESION','CERRAR_CAJA','REABRIR_CAJA','AJUSTE_CAJA','SOLICITAR_RESET','RESTABLECER_CLAVE'";

    private const ENUM_ANTERIOR = "'CREAR','ACTUALIZAR','ANULAR','ELIMINAR','VER','EXPORTAR','IMPRIMIR','INICIAR_SESION','CERRAR_SESION','CERRAR_CAJA','REABRIR_CAJA','AJUSTE_CAJA'";

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

        $usos = DB::table('registro_movimientos')
            ->whereIn('accion', ['SOLICITAR_RESET', 'RESTABLECER_CLAVE'])
            ->count();

        if ($usos > 0) {
            throw new RuntimeException(
                "No se puede revertir: existen {$usos} registros de auditoría con acciones de "
                . 'restablecimiento de clave. Elimínalos manualmente si realmente necesitas '
                . 'volver al enum anterior.'
            );
        }

        DB::statement('ALTER TABLE registro_movimientos MODIFY accion ENUM(' . self::ENUM_ANTERIOR . ') NOT NULL');
    }
};
