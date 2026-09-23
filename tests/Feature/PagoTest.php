<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Caja;
use App\Models\Matricula;
use App\Models\ConceptoCobro;
use App\Models\CuentaPorCobrar;
use App\Models\Pago;
use App\Models\PagoDetalle;
use App\Models\ComprobantePago;
use App\Models\MovimientoCaja;
use App\Models\RegistroMovimiento;
use Illuminate\Support\Facades\Hash;

class PagoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_can_create_pago_with_details(): void
    {
        $user = User::factory()->create(['role' => 'secretaria']);
        $this->actingAs($user);

        $matricula = Matricula::factory()->create();
        $caja = Caja::factory()->create(['tipo' => 'EFECTIVO']);
        $concepto = ConceptoCobro::factory()->create();

        $cuenta = CuentaPorCobrar::factory()->create([
            'id_matricula' => $matricula->id_matricula,
            'id_concepto' => $concepto->id_concepto,
            'monto_original' => 200.00,
            'creado_por' => User::factory()->create()->id,
        ]);

        $response = $this->post('/pagos', [
            'codigo' => 'PAG-TEST-001',
            'id_matricula' => $matricula->id_matricula,
            'id_caja' => $caja->id_caja,
            'fecha_pago' => now()->format('Y-m-d'),
            'metodo_pago' => 'EFECTIVO',
            'monto_total' => '100.00',
            'cuentas' => [
                ['id_cuenta' => $cuenta->id_cuenta, 'monto' => '100.00'],
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $pago = Pago::where('codigo', 'PAG-TEST-001')->first();
        $this->assertNotNull($pago);
        $this->assertSame('CONFIRMADO', $pago->estado);
        $this->assertDatabaseHas('pago_detalles', [
            'id_pago' => $pago->id_pago,
            'id_cuenta' => $cuenta->id_cuenta,
        ]);
    }

    public function test_pago_calculates_total_correctly(): void
    {
        $user = User::factory()->create(['role' => 'secretaria']);
        $this->actingAs($user);

        $matricula = Matricula::factory()->create();
        $caja = Caja::factory()->create(['tipo' => 'EFECTIVO']);
        $concepto = ConceptoCobro::factory()->create();

        $cuenta = CuentaPorCobrar::factory()->create([
            'id_matricula' => $matricula->id_matricula,
            'id_concepto' => $concepto->id_concepto,
            'monto_original' => 200.00,
            'creado_por' => User::factory()->create()->id,
        ]);

        $response = $this->post('/pagos', [
            'codigo' => 'PAG-CALC-001',
            'id_matricula' => $matricula->id_matricula,
            'id_caja' => $caja->id_caja,
            'fecha_pago' => now()->format('Y-m-d'),
            'metodo_pago' => 'EFECTIVO',
            'monto_total' => '150.00',
            'cuentas' => [
                ['id_cuenta' => $cuenta->id_cuenta, 'monto' => '150.00'],
            ],
        ]);

        $response->assertSessionHasNoErrors();

        $pago = Pago::where('codigo', 'PAG-CALC-001')->first();
        $this->assertSame(150.0, (float) $pago->monto_total);
    }

    public function test_transaction_rolls_back_on_error(): void
    {
        $user = User::factory()->create(['role' => 'secretaria']);
        $this->actingAs($user);

        $matricula = Matricula::factory()->create();
        $caja = Caja::factory()->create(['tipo' => 'EFECTIVO']);
        $concepto = ConceptoCobro::factory()->create();

        $cuenta = CuentaPorCobrar::factory()->create([
            'id_matricula' => $matricula->id_matricula,
            'id_concepto' => $concepto->id_concepto,
            'monto_original' => 100.00,
            'creado_por' => User::factory()->create()->id,
        ]);

        $response = $this->post('/pagos', [
            'codigo' => 'PAG-ROLLBACK-001',
            'id_matricula' => $matricula->id_matricula,
            'id_caja' => $caja->id_caja,
            'fecha_pago' => now()->format('Y-m-d'),
            'metodo_pago' => 'EFECTIVO',
            'monto_total' => '250.00',
            'cuentas' => [
                ['id_cuenta' => $cuenta->id_cuenta, 'monto' => '250.00'],
            ],
        ]);

        $response->assertSessionHasErrors('cuentas');
        $this->assertDatabaseMissing('pagos', ['codigo' => 'PAG-ROLLBACK-001']);
    }

    public function test_cajero_cannot_delete_pago(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $pago = Pago::create([
            'codigo' => 'PAG-DELETE-001',
            'id_matricula' => Matricula::factory()->create()->id_matricula,
            'id_caja' => Caja::factory()->create()->id_caja,
            'fecha_pago' => now(),
            'metodo_pago' => 'EFECTIVO',
            'monto_total' => 100,
            'estado' => 'CONFIRMADO',
            'registrado_por' => $user->id,
        ]);

        $response = $this->delete('/pagos/' . $pago->id_pago);

        $response->assertStatus(403);
        $this->assertDatabaseHas('pagos', ['id_pago' => $pago->id_pago]);
    }

    public function test_pago_parcial_deja_la_cuenta_en_PARCIAL(): void
    {
        $user = User::factory()->create(['role' => 'secretaria']);
        $this->actingAs($user);

        $matricula = Matricula::factory()->create();
        $caja = Caja::factory()->create(['tipo' => 'EFECTIVO']);
        $concepto = ConceptoCobro::factory()->create();

        $cuenta = CuentaPorCobrar::factory()->create([
            'id_matricula' => $matricula->id_matricula,
            'id_concepto' => $concepto->id_concepto,
            'monto_original' => 200.00,
            'creado_por' => User::factory()->create()->id,
        ]);

        $this->post('/pagos', [
            'codigo' => 'PAG-PARCIAL-001',
            'id_matricula' => $matricula->id_matricula,
            'id_caja' => $caja->id_caja,
            'fecha_pago' => now()->format('Y-m-d'),
            'metodo_pago' => 'EFECTIVO',
            'monto_total' => '80.00',
            'cuentas' => [
                ['id_cuenta' => $cuenta->id_cuenta, 'monto' => '80.00'],
            ],
        ]);

        $cuenta->refresh();
        $this->assertSame('PARCIAL', $cuenta->estado);
    }

    public function test_no_se_puede_pagar_mas_del_saldo_pendiente(): void
    {
        $user = User::factory()->create(['role' => 'secretaria']);
        $this->actingAs($user);

        $datos = [
            'matricula' => Matricula::factory()->create(),
            'caja' => Caja::factory()->create(['tipo' => 'EFECTIVO']),
            'concepto' => ConceptoCobro::factory()->create(),
        ];

        $cuenta = CuentaPorCobrar::factory()->create([
            'id_matricula' => $datos['matricula']->id_matricula,
            'id_concepto' => $datos['concepto']->id_concepto,
            'monto_original' => 100.00,
            'creado_por' => User::factory()->create()->id,
        ]);

        $response = $this->post('/pagos', [
            'codigo' => 'PAG-EXCESO-001',
            'id_matricula' => $datos['matricula']->id_matricula,
            'id_caja' => $datos['caja']->id_caja,
            'fecha_pago' => now()->format('Y-m-d'),
            'metodo_pago' => 'EFECTIVO',
            'monto_total' => '250.00',
            'cuentas' => [
                ['id_cuenta' => $cuenta->id_cuenta, 'monto' => '250.00'],
            ],
        ]);

        $response->assertSessionHasErrors('cuentas');
        $this->assertDatabaseMissing('pagos', ['codigo' => 'PAG-EXCESO-001']);
    }

    public function test_el_monto_total_debe_coincidir_con_la_suma_de_detalles(): void
    {
        $user = User::factory()->create(['role' => 'secretaria']);
        $this->actingAs($user);

        $datos = [
            'matricula' => Matricula::factory()->create(),
            'caja' => Caja::factory()->create(['tipo' => 'EFECTIVO']),
            'concepto' => ConceptoCobro::factory()->create(),
        ];

        $cuenta = CuentaPorCobrar::factory()->create([
            'id_matricula' => $datos['matricula']->id_matricula,
            'id_concepto' => $datos['concepto']->id_concepto,
            'monto_original' => 100.00,
            'creado_por' => User::factory()->create()->id,
        ]);

        $response = $this->post('/pagos', [
            'codigo' => 'PAG-TOTAL-001',
            'id_matricula' => $datos['matricula']->id_matricula,
            'id_caja' => $datos['caja']->id_caja,
            'fecha_pago' => now()->format('Y-m-d'),
            'metodo_pago' => 'EFECTIVO',
            'monto_total' => '50.00',
            'cuentas' => [
                ['id_cuenta' => $cuenta->id_cuenta, 'monto' => '100.00'],
            ],
        ]);

        $response->assertSessionHasErrors('monto_total');
        $this->assertDatabaseMissing('pagos', ['codigo' => 'PAG-TOTAL-001']);
    }
}
