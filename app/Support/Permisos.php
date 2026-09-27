<?php

namespace App\Support;

/**
 * Fuente única de verdad de los permisos por rol.
 *
 * Tanto el middleware RolePermissionMiddleware como el menú lateral
 * consultan esta clase, de modo que un enlace nunca muestra un módulo
 * que el usuario recibiría con 403.
 */
class Permisos
{
    /**
     * Módulos que la secretaría puede administrar.
     */
    public const MODULOS_SECRETARIA = [
        'alumnos', 'apoderados', 'matriculas', 'pagos',
        'cuentas-por-cobrar', 'conceptos', 'categorias', 'cajas',
        'periodos', 'niveles', 'grados', 'facultades', 'ciclos',
    ];

    /**
     * Acciones sensibles restringidas para la secretaría.
     */
    public const ACCIONES_RESERVADAS_SECRETARIA = [
        'pagos.anular',
        'gastos.anular',
        'cuentas-por-cobrar.anular',
    ];

    /**
     * Gestión de cuentas reservada al administrador.
     *
     * El gerente solo puede CONSULTAR el módulo de usuarios (listar y ver).
     * No puede crear cuentas ni borrarlas, y tampoco editarlas: si pudiera
     * enviar un PUT /users/{id} con role=admin se auto-promovía a
     * administrador y se saltaba la única barrera que queda.
     */
    public const GESTION_USUARIOS_SOLO_ADMIN = [
        'users.create',
        'users.store',
        'users.edit',
        'users.update',
        'users.destroy',
    ];

    /**
     * Rutas concretas permitidas al cajero.
     */
    public const RUTAS_CAJERO = [
        'pagos.create',
        'pagos.store',
        'gastos.create',
        'gastos.store',
    ];

    /**
     * Prefijos de ruta permitidos al cajero (solo lectura).
     */
    public const PREFIJOS_CAJERO = [
        'cajas.index',
        'cajas.show',
    ];

    public static function permite(?string $role, ?string $routeName): bool
    {
        if ($routeName === null || $routeName === '') {
            return false;
        }

        return match ($role) {
            'admin' => true,

            'gerente' => self::permiteGerente($routeName),

            'secretaria' => self::permiteSecretaria($routeName),

            'cajero' => self::permiteCajero($routeName),

            default => false,
        };
    }

    private static function permiteGerente(string $routeName): bool
    {
        foreach (self::GESTION_USUARIOS_SOLO_ADMIN as $reservada) {
            if (str_starts_with($routeName, $reservada)) {
                return false;
            }
        }

        return true;
    }

    private static function permiteSecretaria(string $routeName): bool
    {
        foreach (self::ACCIONES_RESERVADAS_SECRETARIA as $reservada) {
            if (str_starts_with($routeName, $reservada)) {
                return false;
            }
        }

        foreach (self::MODULOS_SECRETARIA as $modulo) {
            if (str_starts_with($routeName, $modulo . '.')) {
                return true;
            }
        }

        return false;
    }

    private static function permiteCajero(string $routeName): bool
    {
        if (in_array($routeName, self::RUTAS_CAJERO, true)) {
            return true;
        }

        foreach (self::PREFIJOS_CAJERO as $prefijo) {
            if (str_starts_with($routeName, $prefijo)) {
                return true;
            }
        }

        return false;
    }
}
