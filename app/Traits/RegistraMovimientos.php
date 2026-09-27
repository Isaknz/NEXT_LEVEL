<?php

namespace App\Traits;

use App\Models\RegistroMovimiento;

trait RegistraMovimientos
{
    /**
     * Acciones admitidas por registro_movimientos.accion.
     *
     * SQLite no valida los ENUM, por lo que un valor inventado pasa los tests
     * SQLite y solo revienta (o se pierde en silencio) contra MySQL. Esta lista
     * es la fuente de verdad y la migración
     * 2026_09_26_000001_add_caja_actions_to_registro_movimientos_enum
     * mantiene el enum de la base alineado con ella.
     */
    public const ACCIONES_AUDITABLES = [
        'CREAR',
        'ACTUALIZAR',
        'ANULAR',
        'ELIMINAR',
        'VER',
        'EXPORTAR',
        'IMPRIMIR',
        'INICIAR_SESION',
        'CERRAR_SESION',
        'CERRAR_CAJA',
        'REABRIR_CAJA',
        'AJUSTE_CAJA',
        'SOLICITAR_RESET',
        'RESTABLECER_CLAVE',
    ];

    public static function registrarMovimiento($accion, $modulo, $entidad = null, $entidad_id = null, $descripcion = null, $valores_anteriores = null, $valores_nuevos = null)
    {
        if (! in_array($accion, self::ACCIONES_AUDITABLES, true)) {
            \Log::error("Acción de auditoría no permitida: '{$accion}' en el módulo '{$modulo}'. Se-reverse a ACTUALIZAR.");

            $accion = 'ACTUALIZAR';
        }

        try {
            RegistroMovimiento::create([
                'user_id' => auth()->id(),
                'accion' => $accion,
                'modulo' => $modulo,
                'entidad' => $entidad,
                'entidad_id' => $entidad_id,
                'descripcion' => $descripcion,
                'valores_anteriores' => $valores_anteriores,
                'valores_nuevos' => $valores_nuevos,
                'ip_address' => request()->ip(),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al registrar movimiento: ' . $e->getMessage());
        }
    }
}
