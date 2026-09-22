<?php

namespace Database\Factories;

use App\Models\ConceptoCobro;
use App\Models\CuentaPorCobrar;
use App\Models\Matricula;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CuentaPorCobrar>
 */
class CuentaPorCobrarFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id_matricula' => Matricula::factory(),
            'id_concepto' => ConceptoCobro::factory(),
            'referencia' => 'REF-' . fake()->unique()->numerify('#####'),
            'descripcion' => fake()->optional()->sentence(6),
            'fecha_emision' => now()->format('Y-m-d'),
            'fecha_vencimiento' => now()->addMonth()->format('Y-m-d'),
            'monto_original' => fake()->randomFloat(2, 50, 500),
            'descuento' => 0,
            'recargo' => 0,
            'estado' => 'PENDIENTE',
            'creado_por' => User::factory(),
        ];
    }
}