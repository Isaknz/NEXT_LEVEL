<?php

namespace Tests\Feature;

use App\Models\Caja;
use App\Models\CategoriaGasto;
use App\Models\Gasto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GastoTest extends TestCase
{
    use RefreshDatabase;

    private function registrarUsuario()
    {
        $user = \App\Models\User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);

        return $user;
    }

    private function datosGasto(string $codigo = 'GAS-TEST-001')
    {
        $categoria = CategoriaGasto::firstOrCreate(['nombre' => 'Servicios'], ['estado' => 'ACTIVO']);
        $caja = Caja::factory()->create(['tipo' => 'EFECTIVO']);

        return [
            'categoria' => $categoria,
            'caja' => $caja,
            'payload' => [
                'codigo' => $codigo,
                'id_categoria_gasto' => $categoria->id_categoria_gasto,
                'id_caja' => $caja->id_caja,
                'fecha_gasto' => now()->format('Y-m-d'),
                'proveedor' => 'Proveedor de Prueba',
                'concepto' => 'Pago de luz',
                'descripcion' => 'Recibo del mes',
                'monto' => '120.50',
                'tipo_comprobante' => 'FACTURA',
                'serie_comprobante' => 'F001',
                'numero_comprobante' => '0001',
            ],
        ];
    }

    public function test_se_puede_registrar_un_gasto_con_movimiento_de_caja(): void
    {
        $this->registrarUsuario();
        $datos = $this->datosGasto();

        $response = $this->post('/gastos', $datos['payload']);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $gasto = Gasto::where('codigo', 'GAS-TEST-001')->first();

        $this->assertNotNull($gasto);
        $this->assertSame('REGISTRADO', $gasto->estado);
        $this->assertSame(120.5, (float) $gasto->monto);

        $this->assertDatabaseHas('movimientos_caja', [
            'id_gasto' => $gasto->id_gasto,
            'tipo' => 'EGRESO',
            'estado' => 'ACTIVO',
        ]);

        $this->assertDatabaseHas('registro_movimientos', [
            'modulo' => 'Gastos',
            'accion' => 'CREAR',
            'entidad_id' => $gasto->id_gasto,
        ]);
    }

    public function test_anular_un_gasto_anula_su_movimiento(): void
    {
        $this->registrarUsuario();
        $datos = $this->datosGasto('GAS-ANULAR-001');

        $this->post('/gastos', $datos['payload']);

        $gasto = Gasto::where('codigo', 'GAS-ANULAR-001')->firstOrFail();

        $response = $this->post('/gastos/' . $gasto->id_gasto . '/anular', [
            'motivo_anulacion' => 'Gasto duplicado',
        ]);

        $response->assertSessionHasNoErrors();

        $gasto->refresh();
        $this->assertSame('ANULADO', $gasto->estado);

        $this->assertDatabaseHas('movimientos_caja', [
            'id_gasto' => $gasto->id_gasto,
            'estado' => 'ANULADO',
        ]);
    }

    public function test_no_se_duplican_codigos_de_gasto(): void
    {
        $this->registrarUsuario();
        $datos = $this->datosGasto('GAS-UNICO-001');

        $this->post('/gastos', $datos['payload']);
        $response = $this->post('/gastos', $datos['payload']);

        $response->assertSessionHasErrors('codigo');
        $this->assertSame(1, Gasto::where('codigo', 'GAS-UNICO-001')->count());
    }
}