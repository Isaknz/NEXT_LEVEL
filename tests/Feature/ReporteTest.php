<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Alumno;
use App\Models\Matricula;
use App\Models\CuentaPorCobrar;
use App\Models\ConceptoCobro;
use App\Models\Caja;
use App\Models\Pago;
use Illuminate\Support\Facades\Hash;

class ReporteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_can_export_csv(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);

        $response = $this->get('/reportes/export?format=csv');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    }

    public function test_can_view_deudores(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);

        $matricula = Matricula::factory()->create();
        $concepto = ConceptoCobro::factory()->create();
        CuentaPorCobrar::factory()->create([
            'id_matricula' => $matricula->id_matricula,
            'id_concepto' => $concepto->id_concepto,
            'monto_original' => 100.00,
            'estado' => 'PENDIENTE',
            'creado_por' => User::factory()->create()->id,
        ]);

        $response = $this->get('/reportes');

        $response->assertStatus(200);
    }

    public function test_can_view_vencidos(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);

        $matricula = Matricula::factory()->create();
        $concepto = ConceptoCobro::factory()->create();
        CuentaPorCobrar::factory()->create([
            'id_matricula' => $matricula->id_matricula,
            'id_concepto' => $concepto->id_concepto,
            'monto_original' => 100.00,
            'estado' => 'PENDIENTE',
            'fecha_vencimiento' => now()->subDays(30),
            'creado_por' => User::factory()->create()->id,
        ]);

        $response = $this->get('/reportes');

        $response->assertStatus(200);
    }

    public function test_la_exportacion_excluye_pagos_anulados(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);

        $matricula = Matricula::factory()->create();
        $caja = Caja::factory()->create();

        Pago::create([
            'codigo' => 'PAG-OK-001',
            'id_matricula' => $matricula->id_matricula,
            'id_caja' => $caja->id_caja,
            'fecha_pago' => now(),
            'metodo_pago' => 'EFECTIVO',
            'monto_total' => 100,
            'estado' => 'CONFIRMADO',
            'registrado_por' => $user->id,
        ]);
        Pago::create([
            'codigo' => 'PAG-AN-001',
            'id_matricula' => $matricula->id_matricula,
            'id_caja' => $caja->id_caja,
            'fecha_pago' => now(),
            'metodo_pago' => 'YAPE',
            'monto_total' => 999,
            'estado' => 'ANULADO',
            'registrado_por' => $user->id,
        ]);

        $response = $this->get('/reportes/export?format=csv');
        $response->assertStatus(200);

        $contenido = $response->streamedContent();
        $this->assertStringContainsString('CONFIRMADO', $contenido);
        $this->assertStringNotContainsString('ANULADO', $contenido);
    }

    public function test_la_exportacion_respeta_el_filtro_de_fechas(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);

        $matricula = Matricula::factory()->create();
        $caja = Caja::factory()->create();

        Pago::create([
            'codigo' => 'PAG-ANT-001',
            'id_matricula' => $matricula->id_matricula,
            'id_caja' => $caja->id_caja,
            'fecha_pago' => now()->subYear(),
            'metodo_pago' => 'PLIN',
            'monto_total' => 100,
            'estado' => 'CONFIRMADO',
            'registrado_por' => $user->id,
        ]);

        $desde = now()->startOfYear()->format('Y-m-d');
        $response = $this->get('/reportes/export?format=csv&desde='.$desde);
        $response->assertStatus(200);

        $contenido = $response->streamedContent();
        $this->assertStringNotContainsString('PLIN', $contenido);
    }
}
