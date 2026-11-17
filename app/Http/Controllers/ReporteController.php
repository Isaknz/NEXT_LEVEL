<?php

namespace App\Http\Controllers;

use App\Exports\ReportesExport;
use App\Traits\RegistraMovimientos;
use App\Models\CuentaPorCobrar;
use App\Models\Pago;
use App\Models\PeriodoAcademico;
use Excel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PDF;

class ReporteController extends Controller
{
    use RegistraMovimientos;

    public function index()
    {
        return view('reportes.index');
    }

    public function deudores(Request $request)
    {
        $periodoId = $this->filtros($request)['periodo'] ?? null;

        $deudores = CuentaPorCobrar::query()
            ->with(['matricula.alumno.apoderado', 'concepto'])
            ->whereIn('estado', ['PENDIENTE', 'PARCIAL'])
            ->when($periodoId, fn ($q) => $q->whereHas(
                'matricula',
                fn ($m) => $m->where('id_periodo', $periodoId)
            ))
            ->orderBy('fecha_vencimiento')
            ->paginate(15)
            ->withQueryString();

        return view('reportes.deudores', [
            'deudores' => $deudores,
            'periodos' => PeriodoAcademico::orderBy('anio', 'desc')->orderBy('nombre')->get(),
            'periodoId' => $periodoId,
        ]);
    }

    public function vencidos(Request $request)
    {
        $periodoId = $this->filtros($request)['periodo'] ?? null;

        $vencidos = CuentaPorCobrar::query()
            ->with(['matricula.alumno', 'concepto'])
            ->whereIn('estado', ['PENDIENTE', 'PARCIAL'])
            ->whereNotNull('fecha_vencimiento')
            ->whereDate('fecha_vencimiento', '<', today())
            ->when($periodoId, fn ($q) => $q->whereHas(
                'matricula',
                fn ($m) => $m->where('id_periodo', $periodoId)
            ))
            ->orderBy('fecha_vencimiento')
            ->paginate(15)
            ->withQueryString();

        return view('reportes.vencidos', [
            'vencidos' => $vencidos,
            'periodos' => PeriodoAcademico::orderBy('anio', 'desc')->orderBy('nombre')->get(),
            'periodoId' => $periodoId,
        ]);
    }

    /**
     * Valida y normaliza los filtros de los reportes.
     */
    private function filtros(Request $request): array
    {
        $datos = $request->validate([
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date'],
            'periodo' => ['nullable', 'integer', 'exists:periodos_academicos,id_periodo'],
        ]);

        if (isset($datos['desde'], $datos['hasta']) && $datos['hasta'] < $datos['desde']) {
            throw ValidationException::withMessages([
                'hasta' => 'La fecha hasta debe ser igual o posterior a la fecha desde.',
            ]);
        }

        return $datos;
    }

    /**
     * Consulta base de pagos confirmados. Se mantiene como Builder para que
     * las exportaciones puedan recorrerla por lotes en lugar de cargar todo
     * el historial en memoria.
     */
    private function pagosFiltrados(array $filtros): Builder
    {
        return Pago::query()
            ->with(['matricula.alumno'])
            ->where('estado', 'CONFIRMADO')
            ->when($filtros['desde'] ?? null, fn ($q, $desde) => $q->whereDate('fecha_pago', '>=', $desde))
            ->when($filtros['hasta'] ?? null, fn ($q, $hasta) => $q->whereDate('fecha_pago', '<=', $hasta))
            ->orderBy('id_pago');
    }

    public function exportExcel(Request $request)
    {
        $filtros = $this->filtros($request);
        $formato = $request->query('format', 'excel');

        $encabezados = ['Alumno', 'Matricula', 'Metodo de Pago', 'Monto', 'Estado'];
        $archivo = 'reportes-'.now()->format('Y-m-d').'.'.($formato === 'excel' ? 'xlsx' : 'csv');
        $query = $this->pagosFiltrados($filtros);

        self::registrarMovimiento(
            'EXPORTAR',
            'Reportes',
            'Pago',
            null,
            "Exportó el reporte de pagos en formato {$formato}"
        );

        if ($formato === 'excel') {
            return Excel::download(new ReportesExport($encabezados, $query), $archivo);
        }

        // El CSV se genera por lotes para no agotar la memoria con miles de pagos.
        return response()->streamDownload(function () use ($encabezados, $query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $encabezados);

            foreach ($query->lazyById(500) as $pago) {
                fputcsv($handle, array_map([$this, 'celdaCsv'], [
                    $pago->matricula->alumno->nombre_completo ?? 'N/A',
                    $pago->matricula->codigo ?? 'N/A',
                    $pago->metodo_pago,
                    number_format((float) $pago->monto_total, 2),
                    $pago->estado,
                ]));
            }

            fclose($handle);
        }, $archivo, ['Content-Type' => 'text/csv']);
    }

    public function exportPdf(Request $request)
    {
        $filtros = $this->filtros($request);

        $reportes = $this->pagosFiltrados($filtros)
            ->orderByDesc('fecha_pago')
            ->get();

        self::registrarMovimiento(
            'EXPORTAR',
            'Reportes',
            'Pago',
            null,
            'Exportó el reporte de pagos en formato pdf'
        );

        $pdf = PDF::loadView('reportes.pdf', compact('reportes'));

        return $pdf->download('reportes-'.now()->format('Y-m-d').'.pdf');
    }

    /**
     * Neutraliza la inyección de fórmulas en CSV: un nombre de alumno como
     * "=HYPERLINK(...)" se ejecutaría al abrir el archivo en Excel.
     */
    private function celdaCsv($valor): string
    {
        $valor = (string) $valor;

        return preg_match('/^[=+\-@\t\r]/', $valor) === 1 ? "'".$valor : $valor;
    }
}
