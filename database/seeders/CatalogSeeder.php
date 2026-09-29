<?php

namespace Database\Seeders;

use App\Models\Caja;
use App\Models\CategoriaGasto;
use App\Models\ConceptoCobro;
use App\Models\Grado;
use App\Models\Nivel;
use App\Models\PeriodoAcademico;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $periodo = PeriodoAcademico::firstOrCreate(
            ['codigo' => 'P' . now()->year],
            [
                'nombre' => 'Periodo ' . now()->year,
                'anio' => now()->year,
                'fecha_inicio' => now()->startOfYear(),
                'fecha_fin' => now()->endOfYear(),
                'estado' => 'ABIERTO',
            ]
        );

        $niveles = [
            ['codigo' => 'PRI', 'nombre' => 'PRIMARIA'],
            ['codigo' => 'SEC', 'nombre' => 'SECUNDARIA'],
            ['codigo' => 'ACA', 'nombre' => 'ACADEMIA'],
        ];

        foreach ($niveles as $nivel) {
            Nivel::firstOrCreate(['nombre' => $nivel['nombre']], $nivel);
        }

        $grados = [
            ['PRIMARIA', ['1°', '2°', '3°', '4°', '5°', '6°']],
            ['SECUNDARIA', ['1°', '2°', '3°', '4°', '5°']],
        ];

        foreach ($grados as [$nombreNivel, $nombres]) {
            $nivel = Nivel::where('nombre', $nombreNivel)->first();
            if (! $nivel) {
                continue;
            }

            foreach ($nombres as $orden => $nombre) {
                Grado::firstOrCreate(
                    ['id_nivel' => $nivel->id_nivel, 'nombre' => $nombre],
                    ['orden' => $orden + 1, 'estado' => 'ACTIVO']
                );
            }
        }

        $cajas = [
            ['codigo' => 'CAJA-EFECTIVO', 'nombre' => 'Caja Efectivo', 'tipo' => 'EFECTIVO'],
            ['codigo' => 'CAJA-BCP', 'nombre' => 'Banco BCP', 'tipo' => 'BANCO'],
            ['codigo' => 'CAJA-YAPE', 'nombre' => 'Yape', 'tipo' => 'BILLETERA_DIGITAL'],
        ];

        foreach ($cajas as $caja) {
            Caja::firstOrCreate(['codigo' => $caja['codigo']], ['estado' => 'ACTIVA'] + $caja);
        }

        $conceptos = [
            ['codigo' => 'MATRICULA', 'nombre' => 'Matrícula', 'tipo' => 'MATRICULA'],
            ['codigo' => 'MENSUALIDAD', 'nombre' => 'Mensualidad', 'tipo' => 'MENSUALIDAD'],
            ['codigo' => 'MATERIAL', 'nombre' => 'Materiales', 'tipo' => 'MATERIAL'],
            ['codigo' => 'EXAMEN', 'nombre' => 'Exámenes', 'tipo' => 'EXAMEN'],
        ];

        foreach ($conceptos as $concepto) {
            ConceptoCobro::firstOrCreate(
                ['codigo' => $concepto['codigo']],
                ['modalidad_aplicable' => 'AMBOS', 'monto_referencial' => 0, 'estado' => 'ACTIVO'] + $concepto
            );
        }

        $categorias = ['Planillas', 'Servicios', 'Materiales', 'Publicidad', 'Mantenimiento', 'Otros'];

        foreach ($categorias as $categoria) {
            CategoriaGasto::firstOrCreate(['nombre' => $categoria], ['estado' => 'ACTIVO']);
        }
    }
}