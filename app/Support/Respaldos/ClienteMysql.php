<?php

namespace App\Support\Respaldos;

use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

/**
 * Ejecuta los binarios de MySQL con credenciales por variable de entorno
 * (para que la contraseña no aparezca en la lista de procesos).
 *
 * En un equipo pueden coexistir varias instalaciones cuyos clientes no
 * son compatibles entre sí, así que cada binario se identifica por una
 * lista de candidatos y se usa el primero que logre conectarse.
 */
class ClienteMysql
{
    /**
     * @param  array{host: string, port: int|string, username: string, password: string, database?: string}  $conexion
     * @param  array<string, array<int, string>>  $binarios
     */
    public function __construct(
        private readonly array $conexion,
        private readonly array $binarios,
    ) {}

    private ?string $binarioUsado = null;

    /**
     * @param  array<int, string>  $argumentos
     * @param  string|null  $entrada  Ruta de un archivo a inyectar por stdin
     */
    public function ejecutar(string $clave, array $argumentos, ?string $entrada = null, int $timeout = 600): Process
    {
        $fallos = [];

        foreach ($this->binarios[$clave] ?? [] as $binario) {
            $proceso = new Process(array_merge([$binario], $argumentos));
            $proceso->setEnv(['MYSQL_PWD' => (string) ($this->conexion['password'] ?? '')]);
            $proceso->setTimeout($timeout);

            if ($entrada !== null) {
                $manejador = @fopen($entrada, 'rb');

                if ($manejador === false) {
                    throw new RuntimeException("No se pudo abrir el archivo de entrada: {$entrada}");
                }

                $proceso->setInput($manejador);
            }

            try {
                $proceso->run();
            } catch (Throwable $e) {
                $fallos[] = "{$binario}: {$e->getMessage()}";

                continue;
            }

            if ($proceso->isSuccessful()) {
                $this->binarioUsado = $binario;

                return $proceso;
            }

            $fallos[] = $binario.' → '.(trim($proceso->getErrorOutput()) ?: 'código '.$proceso->getExitCode());
        }

        if ($fallos === []) {
            throw new RuntimeException("No hay ningún binario disponible para «{$clave}».");
        }

        throw new RuntimeException(implode(' | ', $fallos));
    }

    /**
     * Binario que logró conectarse en la última ejecución.
     */
    public function binarioUsado(): ?string
    {
        return $this->binarioUsado;
    }

    /**
     * Conecta directamente a una base de datos concreta, que puede no ser
     * la configurada por defecto (se usa al restaurar en una base temporal).
     */
    public function pdo(string $base): \PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $this->conexion['host'],
            $this->conexion['port'],
            $base
        );

        return new \PDO($dsn, $this->conexion['username'], $this->conexion['password'], [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        ]);
    }
}
