<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Comprobante de Pago</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #0f2540; padding-bottom: 20px; margin-bottom: 30px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #0f2540; color: white; }
        .total { font-weight: bold; font-size: 1.2em; text-align: right; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1 style="color: #0f2540; margin: 0;">NEXT LEVEL SCHOOL</h1>
            <p style="color: #666; margin: 5px 0 0 0;">Comprobante de Pago</p>
        </div>
        <div style="text-align: right;">
            <p style="margin: 0; font-weight: bold;">Fecha: {{ $pago->created_at->format('d/m/Y') }}</p>
            <p style="margin: 0;">Número: {{ $pago->codigo }}</p>
        </div>
    </div>
    <div class="info-grid">
        <div><strong>Alumno:</strong> {{ $pago->matricula->alumno->nombre_completo }}</div>
        <div><strong>Apoderado:</strong> {{ $pago->matricula->alumno->apoderado->nombre_completo ?? 'N/A' }}</div>
        <div><strong>Matrícula:</strong> {{ $pago->matricula->codigo }}</div>
        <div><strong>Concepto:</strong> {{ $pago->detalles->first()?->cuentaPorCobrar->concepto->nombre ?? 'N/A' }}</div>
        <div><strong>Método:</strong> {{ $pago->metodo_pago }}</div>
        <div><strong>Estado:</strong> {{ $pago->estado }}</div>
    </div>
    <table>
        <thead><tr><th>Concepto</th><th>Monto</th></tr></thead>
        <tbody>
            @foreach($pago->detalles as $detalle)
            <tr><td>{{ $detalle->cuentaPorCobrar->concepto->nombre }}</td><td>{{ number_format($detalle->monto, 2) }}</td></tr>
            @endforeach
        </tbody>
    </table>
    <div class="total">Total: {{ number_format($pago->monto_total, 2) }} S/.</div>
</body>
</html>
