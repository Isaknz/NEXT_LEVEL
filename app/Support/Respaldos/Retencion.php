<?php

namespace App\Support\Respaldos;

use Carbon\CarbonInterface;

/**
 * Decide qué respaldos antiguos se pueden eliminar.
 *
 * Dentro de la ventana diaria se conservan todos los respaldos. Fuera de
 * esa ventana se conserva el más reciente de cada semana ISO, hasta un
 * máximo de semanas configurado. La regla es determinista y no toca el
 * sistema de ficheros, por lo que se puede probar de forma aislada.
 */
class Retencion
{
    /**
     * @param  array<int, array{ruta: string, fecha: CarbonInterface}>  $respaldos
     * @return array<int, string> Rutas que deben eliminarse
     */
    public function aEliminar(array $respaldos, CarbonInterface $ahora, int $dias, int $semanas): array
    {
        $dias = max(0, $dias);
        $semanas = max(0, $semanas);

        $limiteDiario = $ahora->copy()->subDays($dias)->startOfDay();

        $antiguos = [];
        foreach ($respaldos as $respaldo) {
            if ($respaldo['fecha']->lessThan($limiteDiario)) {
                $antiguos[] = $respaldo;
            }
        }

        // Un representante por semana ISO: el respaldo más reciente.
        $porSemana = [];
        foreach ($antiguos as $respaldo) {
            $semana = $respaldo['fecha']->format('o-W');

            if (! isset($porSemana[$semana]) || $respaldo['fecha']->greaterThan($porSemana[$semana]['fecha'])) {
                $porSemana[$semana] = $respaldo;
            }
        }

        // De las semanas disponibles se conservan solo las más recientes.
        uasort($porSemana, fn (array $a, array $b) => $b['fecha']->getTimestamp() <=> $a['fecha']->getTimestamp());
        $semanasConservadas = array_slice($porSemana, 0, $semanas, true);

        $conservar = [];
        foreach ($semanasConservadas as $respaldo) {
            $conservar[$respaldo['ruta']] = true;
        }

        $eliminar = [];
        foreach ($antiguos as $respaldo) {
            if (! isset($conservar[$respaldo['ruta']])) {
                $eliminar[] = $respaldo['ruta'];
            }
        }

        return $eliminar;
    }
}
