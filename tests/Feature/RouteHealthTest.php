<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;
use App\Models\User;

/**
 * Guarda de regresión: verifica que ninguna ruta GET de la aplicación
 * responda con error 5xx para un usuario administrador.
 *
 * Se agregó porque la suite original no cubría las rutas de detalle
 * (show/edit) ni las vistas de reportes, y esas rutas fallaban en silencio.
 */
class RouteHealthTest extends TestCase
{
    use RefreshDatabase;

    private function httpStatus(string $method, string $uri, array $data = []): int
    {
        try {
            return $this->{$method}($uri, $data)->getStatusCode();
        } catch (\Throwable) {
            return 599;
        }
    }

    public function test_rutas_get_sin_parametros_no_fallan(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $omitidas = [
            'login', 'register', 'password.request', 'password.reset',
            'verification.notice', 'verification.verify', 'password.confirm',
            'storage.local', 'verification.send', 'logout',
        ];

        $fallas = [];

        foreach (Route::getRoutes() as $ruta) {
            $nombre = $ruta->getName();

            if (! $nombre
                || ! in_array('GET', $ruta->methods(), true)
                || in_array($nombre, $omitidas, true)
                || $ruta->uri() === 'up'
                || str_contains($ruta->uri(), '{')) {
                continue;
            }

            $estado = $this->httpStatus('GET', '/' . ltrim($ruta->uri(), '/'));

            if ($estado >= 500) {
                $fallas[] = "{$nombre} (/{$ruta->uri()}) devolvio {$estado}";
            }
        }

        $this->assertSame([], $fallas, implode(PHP_EOL, $fallas));
    }

    public function test_rutas_de_detalle_no_fallan(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $recursos = [
            'alumnos'     => \App\Models\Alumno::factory()->create()->id_alumno,
            'matriculas'  => \App\Models\Matricula::factory()->create()->id_matricula,
            'cajas'       => \App\Models\Caja::factory()->create()->id_caja,
            'niveles'     => \App\Models\Nivel::factory()->create()->id_nivel,
            'facultades'  => \App\Models\Facultad::factory()->create()->id_facultad,
            'periodos'    => \App\Models\PeriodoAcademico::factory()->create()->id_periodo,
        ];

        $fallas = [];

        foreach ($recursos as $recurso => $id) {
            foreach (["{$recurso}/{$id}", "{$recurso}/{$id}/edit"] as $uri) {
                $estado = $this->httpStatus('GET', '/' . $uri);

                if ($estado >= 500) {
                    $fallas[] = "/{$uri} devolvio {$estado}";
                }
            }
        }

        $this->assertSame([], $fallas, implode(PHP_EOL, $fallas));
    }

    public function test_rutas_de_reportes_no_fallan(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        foreach (['/reportes', '/reportes/deudores', '/reportes/vencidos', '/reportes/export?format=csv'] as $uri) {
            $estado = $this->httpStatus('GET', $uri);

            $this->assertLessThan(
                500,
                $estado,
                "{$uri} devolvio {$estado}: el reporte deberia renderizar correctamente."
            );
        }
    }

    public function test_comprobante_de_pago_se_genera(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $pago = \App\Models\Pago::create([
            'codigo'         => 'PAG-SMOKE',
            'id_matricula'   => \App\Models\Matricula::factory()->create()->id_matricula,
            'id_caja'        => \App\Models\Caja::factory()->create()->id_caja,
            'fecha_pago'     => now(),
            'metodo_pago'    => 'EFECTIVO',
            'monto_total'    => 10,
            'estado'         => 'CONFIRMADO',
            'registrado_por' => auth()->id(),
        ]);

        $respuesta = $this->get("/pagos/{$pago->id_pago}/comprobante");

        $respuesta->assertStatus(200);
        $this->assertStringContainsString('pdf', (string) $respuesta->headers->get('content-type'));
    }
}
