<?php

namespace App\Console\Commands;

use App\Support\Respaldos\ClienteMysql;
use App\Support\Respaldos\Retencion;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;
use ZipArchive;

/**
 * Crea un respaldo comprimido de la base de datos y de los archivos
 * subidos, junto con un manifiesto que permite verificar su integridad.
 */
class RespaldoCrear extends Command
{
    protected $signature = 'respaldo:crear
                            {--directorio= : Carpeta de destino (por defecto la configuración)}
                            {--sin-storage : No incluir los archivos de storage/app}';

    protected $description = 'Crea un respaldo comprimido de la base de datos y de los archivos subidos';

    public function handle(): int
    {
        $conexion = config('database.default');
        $driver = config("database.connections.{$conexion}.driver");

        if ($driver !== 'mysql') {
            $this->error("La conexión usa el driver «{$driver}» y no admite mysqldump.");
            $this->line('Para SQLite basta con copiar el archivo de la base de datos fuera del servidor.');

            return self::FAILURE;
        }

        $carpeta = rtrim($this->option('directorio') ?: config('respaldos.directorio'), '/\\');
        File::ensureDirectoryExists($carpeta);

        $ahora = Carbon::now();
        $nombre = 'nextlevel_db_'.$ahora->format('Y-m-d_His');
        $sql = $carpeta.DIRECTORY_SEPARATOR.$nombre.'.sql';
        $gz = $sql.'.gz';
        $manifiesto = $carpeta.DIRECTORY_SEPARATOR.$nombre.'.json';
        $cliente = $this->cliente($conexion);

        try {
            $this->volcar($cliente, $sql);
            $this->comprimir($sql, $gz);

            $datos = [
                'aplicacion' => config('app.name'),
                'creado_en' => $ahora->toIso8601String(),
                'conexion' => $conexion,
                'base_de_datos' => config("database.connections.{$conexion}.database"),
                'archivo' => basename($gz),
                'bytes' => filesize($gz),
                'sha256' => hash_file('sha256', $gz),
                'commit' => $this->commit(),
                'tablas' => $this->conteo(),
            ];

            $datos['archivos'] = $this->storage($carpeta, $nombre);

            File::put(
                $manifiesto,
                json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL
            );

            $eliminados = $this->podar($carpeta);
        } catch (Throwable $e) {
            File::delete([$sql, $gz, $manifiesto]);

            $this->error('El respaldo falló: '.$e->getMessage());

            return self::FAILURE;
        }

        $filas = array_sum($datos['tablas']);

        $this->info("Respaldo creado: {$gz}");
        $this->line('  Tablas: '.count($datos['tablas']).' · Filas: '.$filas.' · '.$this->tamano($datos['bytes']));
        $this->line('  SHA-256: '.$datos['sha256']);
        $this->line('  Herramienta: '.$cliente->binarioUsado());

        if ($eliminados > 0) {
            $this->line("  Respaldos antiguos eliminados por retención: {$eliminados}");
        }

        return self::SUCCESS;
    }

    protected function cliente(string $conexion): ClienteMysql
    {
        return new ClienteMysql(
            config("database.connections.{$conexion}"),
            config('respaldos'),
        );
    }

    /**
     * Ejecuta mysqldump probando los candidatos configurados hasta que uno
     * logre conectarse con el servidor actual.
     */
    protected function volcar(ClienteMysql $cliente, string $destino): void
    {
        $config = config('database.connections.'.config('database.default'));

        $cliente->ejecutar('mysqldump', [
            '--host='.$config['host'],
            '--port='.$config['port'],
            '--user='.$config['username'],
            '--single-transaction',
            '--quick',
            '--routines',
            '--triggers',
            '--events',
            '--hex-blob',
            '--default-character-set='.($config['charset'] ?? 'utf8mb4'),
            '--result-file='.$destino,
            $config['database'],
        ]);

        if (! is_file($destino) || filesize($destino) === 0) {
            throw new RuntimeException('mysqldump no generó contenido.');
        }
    }

    protected function comprimir(string $origen, string $destino): void
    {
        $entrada = fopen($origen, 'rb');
        $salida = gzopen($destino, 'wb9');

        if ($entrada === false || $salida === false) {
            throw new RuntimeException('No se pudo abrir el volcado para comprimirlo.');
        }

        while (! feof($entrada)) {
            $bloque = fread($entrada, 1024 * 1024);

            if ($bloque !== false && $bloque !== '') {
                gzwrite($salida, $bloque);
            }
        }

        fclose($entrada);
        gzclose($salida);

        File::delete($origen);
    }

    /**
     * Cuenta las filas reales de cada tabla en el momento del respaldo.
     *
     * @return array<string, int>
     */
    protected function conteo(): array
    {
        $tablas = [];

        foreach (DB::select('SHOW TABLES') as $fila) {
            $tabla = array_values((array) $fila)[0];
            $tablas[$tabla] = DB::table($tabla)->count();
        }

        ksort($tablas);

        return $tablas;
    }

    protected function commit(): ?string
    {
        try {
            $proceso = new Process(['git', 'rev-parse', '--short', 'HEAD'], base_path());
            $proceso->setTimeout(10);
            $proceso->run();

            return $proceso->isSuccessful() ? trim($proceso->getOutput()) : null;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @return array{incluido: bool, archivo?: string, bytes?: int, motivo?: string}
     */
    protected function storage(string $carpeta, string $nombre): array
    {
        if ($this->option('sin-storage') || ! config('respaldos.incluir_storage')) {
            return ['incluido' => false, 'motivo' => 'desactivado'];
        }

        $raiz = storage_path('app');
        $carpetaReal = realpath($carpeta);
        $archivos = [];

        foreach (File::allFiles($raiz) as $archivo) {
            $relativa = str_replace('\\', '/', $archivo->getRelativePathname());

            if (str_starts_with($relativa, 'respaldos/') || str_starts_with($archivo->getFilename(), '.')) {
                continue;
            }

            $rutaReal = realpath($archivo->getPathname());

            if ($carpetaReal !== false && $rutaReal !== false
                && str_starts_with($rutaReal, $carpetaReal.DIRECTORY_SEPARATOR)) {
                continue;
            }

            $archivos[] = [$archivo->getPathname(), $relativa];
        }

        if ($archivos === []) {
            return ['incluido' => false, 'motivo' => 'no hay archivos en storage/app'];
        }

        $destino = $carpeta.DIRECTORY_SEPARATOR.$nombre.'_storage.zip';
        $zip = new ZipArchive;

        if ($zip->open($destino, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('No se pudo crear el ZIP de storage/app.');
        }

        foreach ($archivos as [$ruta, $relativa]) {
            $zip->addFile($ruta, $relativa);
        }

        $zip->close();

        return ['incluido' => true, 'archivo' => basename($destino), 'bytes' => filesize($destino)];
    }

    protected function podar(string $carpeta): int
    {
        $respaldos = [];

        foreach (File::glob($carpeta.DIRECTORY_SEPARATOR.'nextlevel_db_*.sql.gz') as $ruta) {
            $respaldos[] = [
                'ruta' => $ruta,
                'fecha' => Carbon::createFromTimestamp(filemtime($ruta)),
            ];
        }

        $eliminar = (new Retencion)->aEliminar(
            $respaldos,
            Carbon::now(),
            (int) config('respaldos.conservar_diarios'),
            (int) config('respaldos.conservar_semanales'),
        );

        foreach ($eliminar as $ruta) {
            $sello = preg_replace('/^nextlevel_db_|\.sql\.gz$/', '', basename($ruta));

            File::delete($ruta);
            File::delete($carpeta.DIRECTORY_SEPARATOR.'nextlevel_db_'.$sello.'.json');
            File::delete($carpeta.DIRECTORY_SEPARATOR.'nextlevel_db_'.$sello.'_storage.zip');
        }

        return count($eliminar);
    }

    protected function tamano(int $bytes): string
    {
        $unidades = ['B', 'KB', 'MB', 'GB'];
        $indice = 0;

        while ($bytes >= 1024 && $indice < count($unidades) - 1) {
            $bytes /= 1024;
            $indice++;
        }

        return round($bytes, 1).' '.$unidades[$indice];
    }
}
