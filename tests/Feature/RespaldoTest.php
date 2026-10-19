<?php

namespace Tests\Feature;

use App\Support\Respaldos\Retencion;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * Pruebas de la política de retención de respaldos y de los mensajes de
 * los comandos `respaldo:*` cuando el motor no es MySQL (el entorno de
 * pruebas usa SQLite en memoria).
 */
class RespaldoTest extends TestCase
{
    private function respaldo(string $fecha, string $ruta): array
    {
        return ['ruta' => $ruta, 'fecha' => Carbon::parse($fecha)];
    }

    /**
     * El entorno de pruebas puede apuntar a SQLite (local) o a MySQL (CI).
     * Estos casos necesitan un motor distinto de MySQL, así que fijan una
     * conexión SQLite explícita para no depender del entorno.
     */
    private function usarSqlite(): void
    {
        config([
            'database.connections.prueba_sqlite' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
            'database.default' => 'prueba_sqlite',
        ]);
    }

    public function test_conserva_todo_dentro_de_la_ventana_diaria(): void
    {
        $ahora = Carbon::parse('2026-10-19 12:00:00');
        $respaldos = [
            $this->respaldo('2026-10-19 03:00:00', 'hoy-03.sql.gz'),
            $this->respaldo('2026-10-18 03:00:00', 'ayer-03.sql.gz'),
            $this->respaldo('2026-10-13 23:59:59', 'antes-del-limite.sql.gz'),
        ];

        $eliminar = (new Retencion)->aEliminar($respaldos, $ahora, 7, 4);

        $this->assertSame([], $eliminar);
    }

    public function test_fuera_de_la_ventana_conserva_el_mas_reciente_de_cada_semana(): void
    {
        $ahora = Carbon::parse('2026-10-19 12:00:00');
        $respaldos = [
            $this->respaldo('2026-10-19 03:00:00', 'hoy.sql.gz'),
            $this->respaldo('2026-10-10 03:00:00', 'semana-41-viejo.sql.gz'),
            $this->respaldo('2026-10-05 03:00:00', 'semana-41-temprano.sql.gz'),
            $this->respaldo('2026-09-28 03:00:00', 'semana-40.sql.gz'),
            $this->respaldo('2026-09-21 03:00:00', 'semana-39.sql.gz'),
        ];

        $eliminar = (new Retencion)->aEliminar($respaldos, $ahora, 7, 2);

        $this->assertSame(
            ['semana-41-temprano.sql.gz', 'semana-39.sql.gz'],
            array_values($eliminar)
        );
    }

    public function test_el_maximo_de_semanas_se_respeta(): void
    {
        $ahora = Carbon::parse('2026-10-19 12:00:00');
        $respaldos = [
            $this->respaldo('2026-10-10 03:00:00', 'semana-41.sql.gz'),
            $this->respaldo('2026-09-28 03:00:00', 'semana-40.sql.gz'),
            $this->respaldo('2026-09-21 03:00:00', 'semana-39.sql.gz'),
        ];

        $eliminar = (new Retencion)->aEliminar($respaldos, $ahora, 7, 1);

        $this->assertSame(['semana-40.sql.gz', 'semana-39.sql.gz'], $eliminar);
    }

    public function test_con_cero_dias_y_cero_semanas_se_eliminan_los_antiguos(): void
    {
        $ahora = Carbon::parse('2026-10-19 12:00:00');
        $respaldos = [
            $this->respaldo('2026-10-19 11:00:00', 'reciente.sql.gz'),
            $this->respaldo('2026-10-19 00:00:00', 'hoy-a-medianoche.sql.gz'),
            $this->respaldo('2026-10-18 23:59:59', 'ayer.sql.gz'),
        ];

        $eliminar = (new Retencion)->aEliminar($respaldos, $ahora, 0, 0);

        $this->assertSame(['ayer.sql.gz'], $eliminar);
    }

    public function test_sin_respaldos_no_elimina_nada(): void
    {
        $eliminar = (new Retencion)->aEliminar([], Carbon::parse('2026-10-19'), 14, 13);

        $this->assertSame([], $eliminar);
    }

    public function test_el_comando_de_respaldo_rechaza_motores_que_no_son_mysql(): void
    {
        $this->usarSqlite();

        $this->artisan('respaldo:crear')
            ->expectsOutputToContain('driver')
            ->assertExitCode(1);
    }

    public function test_el_comando_de_restauracion_rechaza_motores_que_no_son_mysql(): void
    {
        $this->usarSqlite();

        $this->artisan('respaldo:restaurar', ['archivo' => 'no-existe.sql.gz'])
            ->expectsOutputToContain('driver')
            ->assertExitCode(1);
    }

    public function test_la_ventana_diaria_no_depende_de_la_hora_del_respaldo(): void
    {
        $ahora = Carbon::parse('2026-10-19 00:00:01');
        $respaldos = [
            $this->respaldo('2026-10-18 23:59:59', 'ayer.sql.gz'),
            $this->respaldo('2026-10-18 12:00:00', 'ayer-al-mediodia.sql.gz'),
        ];

        $eliminar = (new Retencion)->aEliminar($respaldos, $ahora, 0, 0);

        $this->assertSame(['ayer.sql.gz', 'ayer-al-mediodia.sql.gz'], $eliminar);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }
}
