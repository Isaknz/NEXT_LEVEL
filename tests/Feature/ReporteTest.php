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

        $response = $this->get('/reportes?format=csv');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv');
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
}
