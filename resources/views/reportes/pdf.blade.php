<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reportes - Next Level School</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #0f2540; padding-bottom: 20px; margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #0f2540; color: white; }
        .total { font-weight: bold; font-size: 1.2em; text-align: right; margin-top: 10px; }
        h1 { color: #0f2540; margin: 0; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1>NEXT LEVEL SCHOOL</h1>
            <p style="color: #666; margin: 5px 0 0 0;">Reporte General</p>
        </div>
        <div style="text-align: right;">
            <p style="margin: 0;">Fecha: {{ now()->format('d/m/Y') }}</p>
        </div>
    </div>
    <table>
        <thead>
            <tr><th>Alumno</th><th>Matrícula</th><th>Método</th><th>Monto</th><th>Estado</th></tr>
        </thead>
        <tbody>
            @foreach($reportes as $reporte)
            <tr>
                <td>{{ $reporte->matricula->alumno->nombre_completo ?? 'N/A' }}</td>
                <td>{{ $reporte->matricula->codigo ?? 'N/A' }}</td>
                <td>{{ $reporte->metodo_pago }}</td>
                <td>{{ number_format($reporte->monto_total, 2) }}</td>
                <td>{{ $reporte->estado }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="total">Total: {{ number_format($reportes->sum('monto_total'), 2) }} S/.</div>
</body>
</html>
