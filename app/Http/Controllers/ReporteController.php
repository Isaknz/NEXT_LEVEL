<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\ReportesExport;
use App\Models\CuentaPorCobrar;
use App\Models\Pago;
use App\Models\PeriodoAcademico;
use Excel;
use PDF;

class ReporteController extends Controller
{
    public function index()
    {
        return view('reportes.index');
    }

    public function deudores(Request $request)
    {
        $periodoId = $request->query('periodo');

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
            'deudores'   => $deudores,
            'periodos'   => PeriodoAcademico::orderBy('anio', 'desc')->orderBy('nombre')->get(),
            'periodoId'  => $periodoId,
        ]);
    }

    public function vencidos(Request $request)
    {
        $periodoId = $request->query('periodo');

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
            'vencidos'   => $vencidos,
            'periodos'   => PeriodoAcademico::orderBy('anio', 'desc')->orderBy('nombre')->get(),
            'periodoId'  => $periodoId,
        ]);
    }

    private function datosReporte(): array
    {
        return Pago::query()
            ->with(['matricula.alumno'])
            ->orderBy('fecha_pago', 'desc')
            ->get()
            ->map(fn (Pago $p) => [
                $p->matricula->alumno->nombre_completo ?? 'N/A',
                $p->matricula->codigo ?? 'N/A',
                $p->metodo_pago,
                number_format((float) $p->monto_total, 2),
                $p->estado,
            ])
            ->all();
    }

    public function exportExcel(Request $request)
    {
        $formato = $request->query('format', 'excel');
        $datos = $this->datosReporte();

        $encabezados = ['Alumno', 'Matricula', 'Metodo de Pago', 'Monto', 'Estado'];
        $archivo = 'reportes-' . now()->format('Y-m-d') . '.' . ($formato === 'excel' ? 'xlsx' : 'csv');

        if ($formato === 'excel') {
            return Excel::download(new ReportesExport($encabezados, $datos), $archivo);
        }

        return response()->streamDownload(function () use ($encabezados, $datos) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $encabezados);
            foreach ($datos as $fila) {
                fputcsv($handle, $fila);
            }
            fclose($handle);
        }, $archivo, ['Content-Type' => 'text/csv']);
    }

    public function exportPdf()
    {
        $reportes = Pago::with(['matricula.alumno'])->orderBy('fecha_pago', 'desc')->get();
        $pdf = PDF::loadView('reportes.pdf', compact('reportes'));

        return $pdf->download('reportes-' . now()->format('Y-m-d') . '.pdf');
    }
}
