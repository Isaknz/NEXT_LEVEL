<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Matricula;
use App\Models\PeriodoAcademico;
use App\Models\Grado;
use App\Models\Nivel;
use App\Models\User;
use App\Models\Caja;
use App\Models\ConceptoCobro;
use App\Models\CuentaPorCobrar;
use App\Models\RegistroMovimiento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cubre el módulo de cuentas por cobrar, que antes devolvía páginas en blanco
 * y no persistía nada.
 */
class CuentaPorCobrarTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->admin()->create();
        $this->actingAs($user);

        return $user;
    }

    private function matricula(): Matricula
    {
        $nivel = Nivel::factory()->create();
        $grado = Grado::factory()->create(['id_nivel' => $nivel->id_nivel, 'nombre' => 'Grado CXC', 'orden' => 4]);

        return Matricula::factory()->create([
            'id_nivel' => $nivel->id_nivel,
            'id_grado' => $grado->id_grado,
            'id_periodo' => PeriodoAcademico::factory(),
        ]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'id_matricula' => $this->matricula()->id_matricula,
            'id_concepto' => ConceptoCobro::factory()->create()->id_concepto,
            'referencia' => 'CXC-0001',
            'descripcion' => 'Mensualidad de marzo',
            'fecha_emision' => '2026-03-01',
            'fecha_vencimiento' => '2026-03-31',
            'monto_original' => '500.00',
            'descuento' => '50.00',
            'recargo' => '25.00',
        ], $overrides);
    }

    /**
     * Crea un pago confirmado aplicado a la cuenta indicada.
     * No existen factories de Pago ni de PagoDetalle, así que se construyen a mano.
     */
    private function aplicarPago(CuentaPorCobrar $cuenta, float $monto, User $user): \App\Models\Pago
    {
        $caja = Caja::factory()->create();

        $pago = \App\Models\Pago::create([
            'codigo' => 'PAG-' . $cuenta->id_cuenta . '-' . uniqid(),
            'id_matricula' => $cuenta->id_matricula,
            'id_caja' => $caja->id_caja,
            'fecha_pago' => now(),
            'metodo_pago' => 'EFECTIVO',
            'monto_total' => $monto,
            'estado' => 'CONFIRMADO',
            'registrado_por' => $user->id,
        ]);

        \App\Models\PagoDetalle::create([
            'id_pago' => $pago->id_pago,
            'id_cuenta' => $cuenta->id_cuenta,
            'monto_aplicado' => $monto,
        ]);

        return $pago;
    }

    // -------------------------------------------------------------- PERSISTENCIA

    public function test_store_persiste_la_cuenta_con_totales_calculados(): void
    {
        $this->admin();

        $this->post(route('cuentas-por-cobrar.store'), $this->payload())
            ->assertRedirect(route('cuentas-por-cobrar.index'));

        $this->assertDatabaseHas('cuentas_por_cobrar', [
            'referencia' => 'CXC-0001',
            'descripcion' => 'Mensualidad de marzo',
            'monto_original' => 500.00,
            'descuento' => 50.00,
            'recargo' => 25.00,
            'estado' => 'PENDIENTE',
        ]);

        $cuenta = CuentaPorCobrar::where('referencia', 'CXC-0001')->firstOrFail();

        $this->assertEquals(475.00, (float) $cuenta->monto_total, 'monto_total = original - descuento + recargo');
        $this->assertEquals(475.00, (float) $cuenta->monto_pendiente);
        $this->assertNotNull($cuenta->creado_por);
    }

    public function test_store_ignora_el_monto_pagado_que_antiguamente_se_enviaba(): void
    {
        $this->admin();

        // 'monto_pagado', 'periodo' y 'observaciones' no son columnas reales.
        $this->post(route('cuentas-por-cobrar.store'), $this->payload([
            'monto_pagado' => '999.00',
            'periodo' => '2026-1',
            'observaciones' => 'texto que debe ignorarse',
        ]))->assertRedirect(route('cuentas-por-cobrar.index'));

        $cuenta = CuentaPorCobrar::where('referencia', 'CXC-0001')->firstOrFail();

        $this->assertEquals(475.00, (float) $cuenta->monto_pendiente, 'monto_pagado no debe afectar el saldo');
    }

    public function test_update_persiste_los_cambios(): void
    {
        $this->admin();
        $cuenta = CuentaPorCobrar::factory()->create([
            'referencia' => 'CXC-EDIT',
            'monto_original' => 100.00,
        ]);

        $this->put(route('cuentas-por-cobrar.update', $cuenta), [
            'id_matricula' => $cuenta->id_matricula,
            'id_concepto' => $cuenta->id_concepto,
            'referencia' => 'CXC-EDIT-2',
            'descripcion' => 'Descripción corregida',
            'fecha_emision' => '2026-04-01',
            'fecha_vencimiento' => '2026-04-30',
            'monto_original' => '750.00',
            'descuento' => '0',
            'recargo' => '0',
        ])->assertRedirect(route('cuentas-por-cobrar.index'));

        $this->assertDatabaseHas('cuentas_por_cobrar', [
            'id_cuenta' => $cuenta->id_cuenta,
            'referencia' => 'CXC-EDIT-2',
            'descripcion' => 'Descripción corregida',
            'monto_original' => 750.00,
        ]);
    }

    // -------------------------------------------------------------- VALIDACIÓN

    public function test_rechaza_referencia_duplicada_para_la_misma_matricula_y_concepto(): void
    {
        $this->admin();
        $matricula = $this->matricula();
        $concepto = ConceptoCobro::factory()->create();

        CuentaPorCobrar::factory()->create([
            'id_matricula' => $matricula->id_matricula,
            'id_concepto' => $concepto->id_concepto,
            'referencia' => 'CXC-DUP',
        ]);

        $this->post(route('cuentas-por-cobrar.store'), [
            'id_matricula' => $matricula->id_matricula,
            'id_concepto' => $concepto->id_concepto,
            'referencia' => 'CXC-DUP',
            'fecha_emision' => '2026-03-01',
            'monto_original' => '100.00',
        ])->assertSessionHasErrors('referencia');
    }

    public function test_rechaza_concepto_inactivo(): void
    {
        $this->admin();
        $concepto = ConceptoCobro::factory()->create(['estado' => 'INACTIVO']);

        $this->post(route('cuentas-por-cobrar.store'), [
            'id_matricula' => $this->matricula()->id_matricula,
            'id_concepto' => $concepto->id_concepto,
            'referencia' => 'CXC-INACT',
            'fecha_emision' => '2026-03-01',
            'monto_original' => '100.00',
        ])->assertSessionHasErrors('id_concepto');
    }

    public function test_rechaza_vencimiento_anterior_a_la_emision(): void
    {
        $this->admin();

        $this->post(route('cuentas-por-cobrar.store'), $this->payload([
            'fecha_emision' => '2026-03-31',
            'fecha_vencimiento' => '2026-03-01',
        ]))->assertSessionHasErrors('fecha_vencimiento');
    }

    public function test_rechaza_monto_cero(): void
    {
        $this->admin();

        $this->post(route('cuentas-por-cobrar.store'), $this->payload([
            'monto_original' => '0',
        ]))->assertSessionHasErrors('monto_original');
    }

    // ------------------------------------------------------------------ ANULAR

    public function test_anular_marca_la_cuenta_como_anulada_con_motivo(): void
    {
        $this->admin();
        $cuenta = CuentaPorCobrar::factory()->create(['estado' => 'PENDIENTE']);

        $this->post(route('cuentas-por-cobrar.anular', $cuenta), [
            'motivo_anulacion' => 'Error en el monto generado',
        ])->assertRedirect(route('cuentas-por-cobrar.index'));

        $this->assertDatabaseHas('cuentas_por_cobrar', [
            'id_cuenta' => $cuenta->id_cuenta,
            'estado' => 'ANULADA',
            'motivo_anulacion' => 'Error en el monto generado',
        ]);

        $cuenta->refresh();
        $this->assertNotNull($cuenta->anulado_at);
    }

    public function test_anular_exige_motivo(): void
    {
        $this->admin();
        $cuenta = CuentaPorCobrar::factory()->create(['estado' => 'PENDIENTE']);

        $this->post(route('cuentas-por-cobrar.anular', $cuenta), [])
            ->assertSessionHasErrors('motivo_anulacion');

        $this->assertDatabaseHas('cuentas_por_cobrar', ['id_cuenta' => $cuenta->id_cuenta, 'estado' => 'PENDIENTE']);
    }

    public function test_no_se_puede_anular_dos_veces(): void
    {
        $this->admin();
        $cuenta = CuentaPorCobrar::factory()->create(['estado' => 'ANULADA']);

        $this->from(route('cuentas-por-cobrar.index'))
            ->post(route('cuentas-por-cobrar.anular', $cuenta), ['motivo_anulacion' => 'Otro intento'])
            ->assertSessionHas('error');
    }

    public function test_no_se_puede_editar_una_cuenta_con_pagos_aplicados(): void
    {
        $user = $this->admin();
        $cuenta = CuentaPorCobrar::factory()->create(['estado' => 'PENDIENTE', 'monto_original' => 500.00]);
        $this->aplicarPago($cuenta, 100.00, $user);

        $this->from(route('cuentas-por-cobrar.index'))
            ->get(route('cuentas-por-cobrar.edit', $cuenta))
            ->assertSessionHas('error');

        $this->from(route('cuentas-por-cobrar.index'))
            ->put(route('cuentas-por-cobrar.update', $cuenta), [
                'id_matricula' => $cuenta->id_matricula,
                'id_concepto' => $cuenta->id_concepto,
                'referencia' => $cuenta->referencia,
                'fecha_emision' => '2026-01-01',
                'monto_original' => '9999.00',
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseHas('cuentas_por_cobrar', [
            'id_cuenta' => $cuenta->id_cuenta,
            'monto_original' => 500.00,
        ]);
    }

    public function test_no_se_puede_eliminar_una_cuenta_con_pagos(): void
    {
        $user = $this->admin();
        $cuenta = CuentaPorCobrar::factory()->create();
        $this->aplicarPago($cuenta, 50.00, $user);

        $this->from(route('cuentas-por-cobrar.index'))
            ->delete(route('cuentas-por-cobrar.destroy', $cuenta))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('cuentas_por_cobrar', ['id_cuenta' => $cuenta->id_cuenta]);
    }

    public function test_destroy_se_elimina_sin_pagos(): void
    {
        $this->admin();
        $cuenta = CuentaPorCobrar::factory()->create();

        $this->delete(route('cuentas-por-cobrar.destroy', $cuenta))
            ->assertRedirect(route('cuentas-por-cobrar.index'));

        $this->assertDatabaseMissing('cuentas_por_cobrar', ['id_cuenta' => $cuenta->id_cuenta]);
    }

    // ------------------------------------------------------------------ ÍNDICE

    public function test_index_filtra_por_estado(): void
    {
        $this->admin();
        CuentaPorCobrar::factory()->create(['estado' => 'PENDIENTE', 'referencia' => 'FIL-PENDIENTE']);
        CuentaPorCobrar::factory()->create(['estado' => 'PAGADA', 'referencia' => 'FIL-PAGADA']);

        $response = $this->get(route('cuentas-por-cobrar.index', ['estado' => 'PENDIENTE']));

        $response->assertOk();
        $response->assertSee('FIL-PENDIENTE');
        $response->assertDontSee('FIL-PAGADA');
    }

    public function test_index_filtra_cuentas_vencidas(): void
    {
        $this->admin();
        CuentaPorCobrar::factory()->create([
            'estado' => 'PENDIENTE',
            'referencia' => 'VENCIDA-01',
            'fecha_vencimiento' => now()->subDay()->format('Y-m-d'),
        ]);
        CuentaPorCobrar::factory()->create([
            'estado' => 'PENDIENTE',
            'referencia' => 'VIGENTE-01',
            'fecha_vencimiento' => now()->addMonth()->format('Y-m-d'),
        ]);

        $response = $this->get(route('cuentas-por-cobrar.index', ['vencidas' => 1]));

        $response->assertOk();
        $response->assertSee('VENCIDA-01');
        $response->assertDontSee('VIGENTE-01');
    }

    public function test_index_busqueda_por_alumno(): void
    {
        $this->admin();

        $alumno = Alumno::factory()->create(['nombres' => 'Zulema', 'apellidos' => 'Apellidounico']);
        $grado = Grado::factory()->create(['nombre' => 'Grado Busqueda', 'orden' => 8]);
        $nivel = $grado->nivel_id ?? null;
        $matricula = Matricula::factory()->create([
            'id_alumno' => $alumno->id_alumno,
            'id_grado' => $grado->id_grado,
            'id_nivel' => $nivel ?: $grado->id_nivel,
        ]);

        $mine = CuentaPorCobrar::factory()->create([
            'id_matricula' => $matricula->id_matricula,
            'referencia' => 'BUSCA-MIA',
        ]);
        CuentaPorCobrar::factory()->create(['referencia' => 'OTRA-CUENTA']);

        $response = $this->get(route('cuentas-por-cobrar.index', ['busqueda' => 'Apellidounico']));

        $response->assertOk();
        $response->assertSee('BUSCA-MIA');
        $response->assertDontSee('OTRA-CUENTA');
    }

    public function test_paginas_del_modulo_responden(): void
    {
        $this->admin();

        $this->get(route('cuentas-por-cobrar.index'))->assertOk();
        $this->get(route('cuentas-por-cobrar.create'))->assertOk();
    }

    // ------------------------------------------------------------- AUDITORÍA

    public function test_las_operaciones_quedan_registradas_en_auditoria(): void
    {
        $this->admin();

        $this->post(route('cuentas-por-cobrar.store'), $this->payload());
        $cuenta = CuentaPorCobrar::where('referencia', 'CXC-0001')->firstOrFail();

        $this->assertDatabaseHas('registro_movimientos', [
            'accion' => 'CREAR',
            'modulo' => 'cuentas_por_cobrar',
            'entidad_id' => $cuenta->id_cuenta,
        ]);

        $this->put(route('cuentas-por-cobrar.update', $cuenta), [
            'id_matricula' => $cuenta->id_matricula,
            'id_concepto' => $cuenta->id_concepto,
            'referencia' => 'CXC-0001-A',
            'fecha_emision' => '2026-03-01',
            'monto_original' => '500.00',
        ])->assertRedirect(route('cuentas-por-cobrar.index'));

        $this->assertDatabaseHas('registro_movimientos', [
            'accion' => 'ACTUALIZAR',
            'modulo' => 'cuentas_por_cobrar',
            'entidad_id' => $cuenta->id_cuenta,
        ]);

        $this->post(route('cuentas-por-cobrar.anular', $cuenta), ['motivo_anulacion' => 'Prueba de auditoría']);

        $this->assertDatabaseHas('registro_movimientos', [
            'accion' => 'ANULAR',
            'modulo' => 'cuentas_por_cobrar',
            'entidad_id' => $cuenta->id_cuenta,
        ]);
    }

    // -------------------------------------------------------------- PERMISOS

    public function test_la_secretaria_no_puede_anular_cuentas(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'secretaria']));
        $cuenta = CuentaPorCobrar::factory()->create(['estado' => 'PENDIENTE']);

        $this->post(route('cuentas-por-cobrar.anular', $cuenta), ['motivo_anulacion' => 'No permitido'])
            ->assertForbidden();

        $this->assertDatabaseHas('cuentas_por_cobrar', ['id_cuenta' => $cuenta->id_cuenta, 'estado' => 'PENDIENTE']);
    }

    public function test_la_secretaria_puede_ver_y_crear_cuentas(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'secretaria']));

        $this->get(route('cuentas-por-cobrar.index'))->assertOk();
        $this->get(route('cuentas-por-cobrar.create'))->assertOk();
    }

    public function test_el_cajero_no_tiene_acceso_al_modulo(): void
    {
        $this->actingAs(User::factory()->cajero()->create());

        $this->get(route('cuentas-por-cobrar.index'))->assertForbidden();
        $this->get(route('cuentas-por-cobrar.create'))->assertForbidden();
    }
}
