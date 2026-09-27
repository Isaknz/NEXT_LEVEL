<?php

namespace Database\Factories;

use App\Models\Caja;
use App\Models\CategoriaGasto;
use App\Models\Gasto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Gasto>
 */
class GastoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'codigo' => 'GAS-' . fake()->unique()->numerify('######'),
            'id_categoria_gasto' => CategoriaGasto::factory(),
            'id_caja' => Caja::factory(),
            'fecha_gasto' => fake()->dateTimeBetween('-6 months', 'now'),
            'proveedor' => fake()->company(),
            'concepto' => fake()->randomElement(['Compra de material', 'Servicio de limpieza', 'Reparación de equipos', 'Transporte', 'Servicios básicos']),
            'descripcion' => fake()->sentence(),
            'monto' => fake()->randomFloat(2, 20, 5000),
            'tipo_comprobante' => fake()->randomElement(['BOLETA', 'FACTURA', 'RECIBO', 'NOTA', 'SIN_COMPROBANTE']),
            'serie_comprobante' => 'B001',
            'numero_comprobante' => fake()->unique()->numerify('#####'),
            'estado' => 'REGISTRADO',
            'registrado_por' => \App\Models\User::factory(),
        ];
    }

    public function anulado(): static
    {
        return $this->state(fn () => [
            'estado' => 'ANULADO',
            'anulado_por' => \App\Models\User::factory(),
            'anulado_at' => now(),
            'motivo_anulacion' => fake()->sentence(),
        ]);
    }
}
