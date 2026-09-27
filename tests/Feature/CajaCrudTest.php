<?php

namespace Tests\Feature;

use App\Models\Caja;
use App\Models\MovimientoCaja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cubre el CRUD de cajas, que antes aceptaba formularios sin persistir nada,
 * y la consistencia del saldo al cerrar.
 */
class CajaCrudTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->admin()->create();
        $this->actingAs($user);

        return $user;
    }

    public function test_store_persiste_la_caja_activa(): void
    {
        $this->admin();

        $this->post(route('cajas.store'), [
            'codigo' => 'CAJA-TEST-01',
            'nombre' => 'Caja de prueba',
            'tipo' => 'EFECTIVO',
            'saldo_inicial' => '250.00',
            'estado' => 'ACTIVA',
        ])->assertRedirect(route('cajas.index'));

        $this->assertDatabaseHas('cajas', [
            'codigo' => 'CAJA-TEST-01',
            'nombre' => 'Caja de prueba',
            'tipo' => 'EFECTIVO',
            'estado' => 'ACTIVA',
            'saldo_inicial' => 250.00,
        ]);
    }

    public function test_store_rechaza_codigo_duplicado(): void
    {
        $this->admin();
        Caja::factory()->create(['codigo' => 'CAJA-DUP']);

        $this->post(route('cajas.store'), [
            'codigo' => 'CAJA-DUP',
            'nombre' => 'Otra caja',
            'tipo' => 'EFECTIVO',
            'estado' => 'ACTIVA',
        ])->assertSessionHasErrors('codigo');
    }

    public function test_store_rechaza_tipo_invalido(): void
    {
        $this->admin();

        $this->post(route('cajas.store'), [
            'codigo' => 'CAJA-TIPO',
            'nombre' => 'Caja con tipo inválido',
            'tipo' => 'CRIPTO',
            'estado' => 'ACTIVA',
        ])->assertSessionHasErrors('tipo');
    }

    public function test_store_no_permite_crear_una_caja_ya_cerrada(): void
    {
        $this->admin();

        $this->post(route('cajas.store'), [
            'codigo' => 'CAJA-CERRADA',
            'nombre' => 'Caja cerrada directa',
            'tipo' => 'EFECTIVO',
            'estado' => 'CERRADA',
        ])->assertSessionHasErrors('estado');
    }

    public function test_update_persiste_los_cambios(): void
    {
        $this->admin();
        $caja = Caja::factory()->create(['nombre' => 'Nombre viejo', 'tipo' => 'EFECTIVO']);

        $this->put(route('cajas.update', $caja), [
            'codigo' => $caja->codigo,
            'nombre' => 'Nombre nuevo',
            'tipo' => 'BANCO',
            'saldo_inicial' => '100.00',
            'estado' => 'ACTIVA',
        ])->assertRedirect(route('cajas.index'));

        $this->assertDatabaseHas('cajas', [
            'id_caja' => $caja->id_caja,
            'nombre' => 'Nombre nuevo',
            'tipo' => 'BANCO',
        ]);
    }

    public function test_no_se_puede_modificar_una_caja_cerrada(): void
    {
        $this->admin();
        $caja = Caja::factory()->create(['estado' => 'CERRADA']);

        $this->from(route('cajas.index'))
            ->put(route('cajas.update', $caja), [
                'codigo' => $caja->codigo,
                'nombre' => 'No debería cambiar',
                'tipo' => $caja->tipo,
                'estado' => 'ACTIVA',
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseHas('cajas', [
            'id_caja' => $caja->id_caja,
            'nombre' => $caja->nombre,
            'estado' => 'CERRADA',
        ]);
    }

    public function test_destroy_se_elimina_sin_movimientos(): void
    {
        $this->admin();
        $caja = Caja::factory()->create();

        $this->delete(route('cajas.destroy', $caja))->assertRedirect(route('cajas.index'));

        $this->assertDatabaseMissing('cajas', ['id_caja' => $caja->id_caja]);
    }

    public function test_destroy_bloqueado_con_movimientos(): void
    {
        $user = $this->admin();
        $caja = Caja::factory()->create();

        MovimientoCaja::create([
            'id_caja' => $caja->id_caja,
            'tipo' => 'INGRESO',
            'origen' => 'AJUSTE',
            'monto' => 100.00,
            'estado' => 'ACTIVO',
            'fecha_movimiento' => now(),
            'registrado_por' => $user->id,
        ]);

        $this->from(route('cajas.index'))
            ->delete(route('cajas.destroy', $caja))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('cajas', ['id_caja' => $caja->id_caja]);
    }

    // ------------------------------------------------------- SALDO Y CIERRE

    public function test_el_ajuste_registra_el_tipo_de_movimiento_correcto(): void
    {
        $this->admin();
        $caja = Caja::factory()->create(['estado' => 'ACTIVA', 'saldo_inicial' => 0]);

        $this->post(route('cajas.ajuste', $caja), [
            'tipo' => 'INGRESO',
            'monto' => '150.00',
            'descripcion' => 'Ajuste de prueba',
        ])->assertRedirect(route('cajas.show', $caja));

        $this->assertDatabaseHas('movimientos_caja', [
            'id_caja' => $caja->id_caja,
            'tipo' => 'AJUSTE_INGRESO',
            'origen' => 'AJUSTE',
            'monto' => 150.00,
        ]);
    }

    public function test_cerrar_calcula_el_saldo_final_con_ingresos_y_egresos(): void
    {
        $user = $this->admin();
        $caja = Caja::factory()->create(['estado' => 'ACTIVA', 'saldo_inicial' => 100.00]);

        foreach ([['INGRESO', 200.00], ['EGRESO', 50.00]] as [$tipo, $monto]) {
            MovimientoCaja::create([
                'id_caja' => $caja->id_caja,
                'tipo' => $tipo,
                'origen' => 'AJUSTE',
                'monto' => $monto,
                'estado' => 'ACTIVO',
                'fecha_movimiento' => now(),
                'registrado_por' => $user->id,
            ]);
        }

        $this->post(route('cajas.cerrar', $caja))->assertRedirect(route('cajas.index'));

        // 100 inicial + 200 ingresos - 50 egresos = 250
        $this->assertDatabaseHas('cajas', [
            'id_caja' => $caja->id_caja,
            'estado' => 'CERRADA',
            'monto_apertura' => 100.00,
            'monto_cierre' => 250.00,
            'usuarios_id_cerrado' => $user->id,
        ]);
    }

    public function test_cerrar_ignora_los_movimientos_anulados(): void
    {
        $user = $this->admin();
        $caja = Caja::factory()->create(['estado' => 'ACTIVA', 'saldo_inicial' => 100.00]);

        MovimientoCaja::create([
            'id_caja' => $caja->id_caja,
            'tipo' => 'INGRESO',
            'origen' => 'PAGO_ALUMNO',
            'monto' => 75.00,
            'estado' => 'ANULADO',
            'fecha_movimiento' => now(),
            'registrado_por' => $user->id,
        ]);

        $this->post(route('cajas.cerrar', $caja));

        $this->assertDatabaseHas('cajas', [
            'id_caja' => $caja->id_caja,
            'monto_cierre' => 100.00,
        ]);
    }

    public function test_aperturar_reabre_una_caja_cerrada(): void
    {
        $this->admin();
        $caja = Caja::factory()->create([
            'estado' => 'CERRADA',
            'monto_cierre' => 300.00,
            'fecha_cierre' => now(),
            'usuarios_id_cerrado' => User::factory()->create()->id,
        ]);

        $this->post(route('cajas.aperturar', $caja))->assertRedirect(route('cajas.index'));

        $this->assertDatabaseHas('cajas', [
            'id_caja' => $caja->id_caja,
            'estado' => 'ACTIVA',
            'usuarios_id_cerrado' => null,
        ]);
    }

    public function test_no_se_puede_registrar_ajuste_en_una_caja_cerrada(): void
    {
        $this->admin();
        $caja = Caja::factory()->create(['estado' => 'CERRADA']);

        $this->post(route('cajas.ajuste', $caja), [
            'tipo' => 'INGRESO',
            'monto' => '50.00',
        ])->assertSessionHas('error');

        $this->assertDatabaseCount('movimientos_caja', 0);
    }

    // ----------------------------------------------------------------- VISTAS

    public function test_las_paginas_de_cajas_responden(): void
    {
        $this->admin();
        $caja = Caja::factory()->create();

        $this->get(route('cajas.index'))->assertOk();
        $this->get(route('cajas.create'))->assertOk();
        $this->get(route('cajas.show', $caja))->assertOk();
        $this->get(route('cajas.edit', $caja))->assertOk();
        $this->get(route('cajas.ajuste.crear', $caja))->assertOk();
    }

    public function test_el_cajero_solo_puede_ver_las_cajas(): void
    {
        $this->actingAs(User::factory()->cajero()->create());
        $caja = Caja::factory()->create();

        $this->get(route('cajas.index'))->assertOk();
        $this->get(route('cajas.show', $caja))->assertOk();

        $this->get(route('cajas.create'))->assertForbidden();
        $this->post(route('cajas.store'), [
            'codigo' => 'CAJA-CAJERO',
            'nombre' => 'No permitido',
            'tipo' => 'EFECTIVO',
            'estado' => 'ACTIVA',
        ])->assertForbidden();
        $this->post(route('cajas.cerrar', $caja))->assertForbidden();

        $this->assertDatabaseMissing('cajas', ['codigo' => 'CAJA-CAJERO']);
    }
}
