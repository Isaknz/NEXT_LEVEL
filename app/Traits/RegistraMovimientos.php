<?php

namespace App\Traits;

use App\Models\RegistroMovimiento;

trait RegistraMovimientos
{
    public static function registrarMovimiento($accion, $modulo, $entidad = null, $entidad_id = null, $descripcion = null, $valores_anteriores = null, $valores_nuevos = null)
    {
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



