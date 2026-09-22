<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Alumno;
use App\Models\Matricula;
use App\Models\Pago;
use App\Models\Gasto;
use App\Models\Caja;
use App\Models\CuentaPorCobrar;
use App\Models\ConceptoCobro;
use Illuminate\Support\Facades\Hash;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_dashboard_shows_all_indicators(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);

        $alumno = Alumno::factory()->create();
        $matricula = Matricula::factory()->create(['id_alumno' => $alumno->id_alumno]);
        $concepto = ConceptoCobro::factory()->create();
        CuentaPorCobrar::factory()->create([
            'id_matricula' => $matricula->id_matricula,
            'id_concepto' => $concepto->id_concepto,
            'monto_original' => 100.00,
            'estado' => 'PENDIENTE',
            'creado_por' => User::factory()->create()->id,
        ]);
        Caja::factory()->create();
        Pago::create([
            'codigo' => 'PAG-DASH-001',
            'id_matricula' => $matricula->id_matricula,
            'id_caja' => Caja::factory()->create()->id_caja,
            'fecha_pago' => now(),
            'metodo_pago' => 'EFECTIVO',
            'monto_total' => 100,
            'estado' => 'CONFIRMADO',
            'registrado_por' => $user->id,
        ]);
        Gasto::create([
            'codigo' => 'GAS-DASH-001',
            'id_categoria_gasto' => \App\Models\CategoriaGasto::factory()->create()->id_categoria_gasto,
            'id_caja' => Caja::factory()->create()->id_caja,
            'fecha_gasto' => now(),
            'concepto' => 'Test',
            'monto' => 50,
            'estado' => 'REGISTRADO',
            'registrado_por' => $user->id,
        ]);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Total Alumnos');
        $response->assertSee('Matrículas Activas');
        $response->assertSee('Total Ingresos');
        $response->assertSee('Total Egresos');
        $response->assertSee('Deudores');
        $response->assertSee('Vencidos');
    }

    public function test_dashboard_shows_total_alumnos(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);

        Alumno::factory()->count(3)->create();

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
    }

    public function test_dashboard_shows_pagos_por_metodo(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);

        $matricula = Matricula::factory()->create();
        $caja = Caja::factory()->create();
        $concepto = ConceptoCobro::factory()->create();
        CuentaPorCobrar::factory()->create([
            'id_matricula' => $matricula->id_matricula,
            'id_concepto' => $concepto->id_concepto,
            'monto_original' => 100.00,
            'creado_por' => User::factory()->create()->id,
        ]);

        Pago::create([
            'codigo' => 'PAG-M-001',
            'id_matricula' => $matricula->id_matricula,
            'id_caja' => $caja->id_caja,
            'fecha_pago' => now(),
            'metodo_pago' => 'EFECTIVO',
            'monto_total' => 100,
            'estado' => 'CONFIRMADO',
            'registrado_por' => $user->id,
        ]);
        Pago::create([
            'codigo' => 'PAG-M-002',
            'id_matricula' => $matricula->id_matricula,
            'id_caja' => $caja->id_caja,
            'fecha_pago' => now(),
            'metodo_pago' => 'YAPE',
            'monto_total' => 50,
            'estado' => 'CONFIRMADO',
            'registrado_por' => $user->id,
        ]);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
    }
}
