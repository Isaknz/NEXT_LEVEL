<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\ReportesExport;
use PDF;

class ReporteController extends Controller
{
    public function index() { return view('reportes.index'); }

    public function export(Request $request)
    {
        $format = $request->query('format');
        if ($format === 'csv') {
            return response()->streamDownload(function() {
                echo "Reporte,Valor\n";
            }, 'reportes.csv', ['Content-Type' => 'text/csv']);
        }
        return redirect()->back();
    }

    public function exportExcel(Request $request)
    {
        $format = $request->query('format', 'excel');
        $data = [];

        $fileName = 'reportes-' . now()->format('Y-m-d') . '.' . ($format === 'excel' ? 'xlsx' : 'csv');

        if ($format === 'excel') {
            return \Excel::download(new ReportesExport($data), $fileName);
        }
        return response()->streamDownload(function() use ($data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Campo1', 'Campo2']);
            foreach ($data as $row) fputcsv($handle, $row);
            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv']);
    }

    public function exportPdf()
    {
        $reportes = \App\Models\Pago::with(['alumno', 'matricula'])->get();
        $pdf = PDF::loadView('reportes.pdf', compact('reportes'));
        return $pdf->download('reportes.pdf');
    }
}
