<?php

namespace App\Console\Commands;

use App\Support\Respaldos\ClienteMysql;
use Illuminate\Console\Command;
use PDO;
use RuntimeException;
use Throwable;

/**
 * Restaura un respaldo creado con respaldo:crear.
 *
 * Por seguridad exige --forzar cuando el destino es la base de datos
 * configurada en la aplicación, y permite restaurar en una base temporal
 * con --destino para ensayar la recuperación sin tocar los datos reales.
 */
class RespaldoRestaurar extends Command
{
    protected $signature = 'respaldo:restaurar
                            {archivo : Ruta del archivo .sql.gz}
                            {--destino= : Base de datos destino (por defecto la configurada)}
                            {--forzar : Confirma la restauración sobre la base configurada}';

    protected $description = 'Restaura un respaldo de la base de datos';

    public function handle(): int
    {
        $archivo = $this->argument('archivo');
        $conexion = config('database.default');
        $config = config("database.connections.{$conexion}");
        $driver = $config['driver'];

        if ($driver !== 'mysql') {
            $this->error("La conexión usa el driver «{$driver}» y no admite la restauración con mysql.");
            $this->line('Para SQLite basta con copiar el archivo de vuelta a su ubicación original.');

            return self::FAILURE;
        }

        if (! is_file($archivo) || filesize($archivo) === 0) {
            $this->error("No existe el archivo o está vacío: {$archivo}");

            return self::FAILURE;
        }

        if (! $this->verificarIntegridad($archivo)) {
            return self::FAILURE;
        }

        $actual = $config['database'];
        $destino = $this->option('destino') ?: $actual;

        if ($destino === $actual && ! $this->option('forzar')) {
            $this->error("El destino es la base de datos en uso ({$actual}).");
            $this->line('Se sobreescribirán todos los datos actuales. Repite con --forzar si es lo que quieres.');

            return self::FAILURE;
        }

        $temporal = null;

        try {
            if ($destino !== $actual) {
                $this->crearBaseSiNoExiste($destino);
            }

            $temporal = $this->descomprimir($archivo);
            $this->restaurar($destino, $temporal);

            $this->line('Restauración completada. Verificando tablas...');
            $conteo = $this->conteo($destino);
        } catch (Throwable $e) {
            $this->error('La restauración falló: '.$e->getMessage());

            return self::FAILURE;
        } finally {
            if ($temporal !== null) {
                @unlink($temporal);
            }
        }

        $this->info("Base de datos «{$destino}» restaurada: ".count($conteo).' tablas, '.array_sum($conteo).' filas.');

        $this->compararConManifiesto($archivo, $conteo);

        return self::SUCCESS;
    }

    /**
     * Compara el SHA-256 del respaldo con el registrado en su manifiesto.
     */
    protected function verificarIntegridad(string $archivo): bool
    {
        $manifiesto = preg_replace('/\.sql\.gz$/', '.json', $archivo);

        if (! is_file($manifiesto)) {
            $this->warn('No hay manifiesto para este respaldo; no se puede verificar el SHA-256.');

            return true;
        }

        $datos = json_decode((string) file_get_contents($manifiesto), true);

        if (! is_array($datos) || ! isset($datos['sha256'])) {
            $this->warn('El manifiesto no contiene un SHA-256 válido; se omite la verificación.');

            return true;
        }

        $real = hash_file('sha256', $archivo);

        if (! hash_equals((string) $datos['sha256'], $real)) {
            $this->error('La integridad del respaldo no coincide con el manifiesto.');
            $this->line("  Esperado: {$datos['sha256']}");
            $this->line("  Obtenido: {$real}");
            $this->line('No se restaura un archivo alterado o incompleto.');

            return false;
        }

        $this->line('Integridad verificada (SHA-256).');

        return true;
    }

    protected function cliente(): ClienteMysql
    {
        return new ClienteMysql(
            config('database.connections.'.$this->conexion()),
            config('respaldos'),
        );
    }

    protected function crearBaseSiNoExiste(string $destino): void
    {
        $config = config("database.connections.{$this->conexion()}");

        $this->cliente()->ejecutar('mysql', [
            '--host='.$config['host'],
            '--port='.$config['port'],
            '--user='.$config['username'],
            '--execute=CREATE DATABASE IF NOT EXISTS `'.$this->escapar($destino).'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci',
        ], null, 60);
    }

    protected function descomprimir(string $archivo): string
    {
        $entrada = gzopen($archivo, 'rb');

        if ($entrada === false) {
            throw new RuntimeException('No se pudo abrir el respaldo comprimido.');
        }

        $temporal = tempnam(sys_get_temp_dir(), 'respaldo_');
        $salida = fopen($temporal, 'wb');

        while (! gzeof($entrada)) {
            $bloque = gzread($entrada, 1024 * 1024);

            if ($bloque !== false && $bloque !== '') {
                fwrite($salida, $bloque);
            }
        }

        gzclose($entrada);
        fclose($salida);

        if (filesize($temporal) === 0) {
            throw new RuntimeException('El respaldo está vacío.');
        }

        return $temporal;
    }

    protected function restaurar(string $destino, string $sql): void
    {
        $config = config("database.connections.{$this->conexion()}");

        $this->cliente()->ejecutar('mysql', [
            '--host='.$config['host'],
            '--port='.$config['port'],
            '--user='.$config['username'],
            '--default-character-set=utf8mb4',
            '--database='.$destino,
        ], $sql, 1800);
    }

    /**
     * Conecta directamente a la base destino (puede no ser la configurada).
     *
     * @return array<string, int>
     */
    protected function conteo(string $destino): array
    {
        $pdo = $this->cliente()->pdo($destino);

        $tablas = [];

        foreach ($pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN) as $tabla) {
            $tablas[$tabla] = (int) $pdo->query('SELECT COUNT(*) FROM `'.$this->escapar($tabla).'`')->fetchColumn();
        }

        ksort($tablas);

        return $tablas;
    }

    /**
     * @param  array<string, int>  $conteo
     */
    protected function compararConManifiesto(string $archivo, array $conteo): void
    {
        $manifiesto = preg_replace('/\.sql\.gz$/', '.json', $archivo);

        if (! is_file($manifiesto)) {
            return;
        }

        $datos = json_decode((string) file_get_contents($manifiesto), true);
        $esperado = is_array($datos) && isset($datos['tablas']) && is_array($datos['tablas'])
            ? $datos['tablas']
            : [];

        if ($esperado === []) {
            return;
        }

        $diferencias = [];

        foreach ($esperado as $tabla => $filas) {
            $actuales = $conteo[$tabla] ?? null;

            if ($actuales === null) {
                $diferencias[] = "{$tabla}: falta en la restauración (esperadas {$filas})";
            } elseif ((int) $filas !== $actuales) {
                $diferencias[] = "{$tabla}: esperadas {$filas}, restauradas {$actuales}";
            }
        }

        if ($diferencias === []) {
            $this->info('Conteo de filas idéntico al manifiesto.');

            return;
        }

        $this->warn('El conteo no coincide con el manifiesto:');

        foreach ($diferencias as $diferencia) {
            $this->line('  - '.$diferencia);
        }
    }

    protected function conexion(): string
    {
        return config('database.default');
    }

    protected function escapar(string $identificador): string
    {
        return str_replace('`', '``', $identificador);
    }
}
