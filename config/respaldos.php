<?php

$windows = PHP_OS_FAMILY === 'Windows';

/*
|--------------------------------------------------------------------------
| Binarios de MySQL
|--------------------------------------------------------------------------
|
| En este equipo conviven varias instalaciones (MySQL 8 de Program Files
| y la de XAMPP, que usa un cliente MariaDB 10.4 incapaz de autenticar
| con caching_sha2_password). Por eso no se fija un único binario: se
| mantiene una lista de candidatos y los comandos usan el primero que
| funcione, probándolos en el orden indicado. En Linux se resuelve desde
| el PATH. Se puede forzar uno concreto con MYSQLDUMP_BIN o MYSQL_BIN.
|
*/

$candidatos = function (string $binario) use ($windows) {
    $rutas = [];

    if ($windows) {
        foreach ([
            'C:\\Program Files\\MySQL\\MySQL Server 8.4\\bin\\',
            'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\',
            'C:\\Program Files\\MySQL\\MySQL Server 5.7\\bin\\',
            'C:\\Program Files\\MariaDB\\bin\\',
            'C:\\xampp\\mysql\\bin\\',
        ] as $ruta) {
            if (is_file($ruta.$binario.'.exe')) {
                $rutas[] = $ruta.$binario.'.exe';
            }
        }
    }

    $rutas[] = $binario;

    return $rutas;
};

$resolver = function (string $variable, string $binario) use ($candidatos) {
    $forzado = env($variable);

    return $forzado ? [$forzado] : $candidatos($binario);
};

return [

    /*
    |--------------------------------------------------------------------------
    | Carpeta de respaldos
    |--------------------------------------------------------------------------
    |
    | Por defecto se usa storage/app/respaldos, que está fuera de public/ y
    | excluido de Git. En un servidor real conviene apuntar a un disco
    | distinto con RESPALDOS_DIRECTORIO (por ejemplo /var/backups/nextlevel)
    | y sincronizar esa carpeta fuera del servidor.
    |
    */

    'directorio' => env('RESPALDOS_DIRECTORIO', storage_path('app/respaldos')),

    /*
    |--------------------------------------------------------------------------
    | Retención
    |--------------------------------------------------------------------------
    |
    | Se conservan todos los respaldos de los últimos N días y, más atrás,
    | el más reciente de cada semana ISO hasta completar N semanas.
    |
    */

    'conservar_diarios' => (int) env('RESPALDOS_DIAS', 14),

    'conservar_semanales' => (int) env('RESPALDOS_SEMANAS', 13),

    'incluir_storage' => filter_var(env('RESPALDOS_INCLUIR_STORAGE', true), FILTER_VALIDATE_BOOL),

    'mysqldump' => $resolver('MYSQLDUMP_BIN', 'mysqldump'),

    'mysql' => $resolver('MYSQL_BIN', 'mysql'),

];
