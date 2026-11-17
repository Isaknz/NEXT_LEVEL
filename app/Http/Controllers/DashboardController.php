<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Apoderado;
use App\Models\Caja;
use App\Models\CuentaPorCobrar;
use App\Models\Gasto;
use App\Models\Matricula;
use App\Models\MovimientoCaja;
use App\Models\Pago;
use App\Models\RegistroMovimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $filtros = $request->validate([
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
            'periodo' => ['nullable', 'integer'],
            'caja' => ['nullable', 'integer'],
        ]);

        $desde = $filtros['desde'] ?? null;
        $hasta = $filtros['hasta'] ?? null;
        $periodo = $filtros['periodo'] ?? null;
        $caja = $filtros['caja'] ?? null;

        $filtrarPagos = fn ($q) => $q
            ->where('estado', 'CONFIRMADO')
            ->when($desde, fn ($q2) => $q2->whereDate('fecha_pago', '>=', $desde))
            ->when($hasta, fn ($q2) => $q2->whereDate('fecha_pago', '<=', $hasta))
            ->when($caja, fn ($q2) => $q2->where('id_caja', $caja))
            ->when($periodo, fn ($q2) => $q2->whereHas('matricula', fn ($m) => $m->where('id_periodo', $periodo)));

        $filtrarGastos = fn ($q) => $q
            ->where('estado', 'REGISTRADO')
            ->when($desde, fn ($q2) => $q2->whereDate('fecha_gasto', '>=', $desde))
            ->when($hasta, fn ($q2) => $q2->whereDate('fecha_gasto', '<=', $hasta))
            ->when($caja, fn ($q2) => $q2->where('id_caja', $caja));

        $totalIngresos = $filtrarPagos(Pago::query())->sum('monto_total');
        $totalEgresos = $filtrarGastos(Gasto::query())->sum('monto');

        // Una sola consulta agrupada en lugar de cargar todos los
        // movimientos de cada caja en memoria.
        $netos = MovimientoCaja::where('estado', 'ACTIVO')
            ->select('id_caja', DB::raw("SUM(CASE WHEN tipo IN ('INGRESO','AJUSTE_INGRESO') THEN monto ELSE -monto END) as neto"))
            ->groupBy('id_caja')
            ->pluck('neto', 'id_caja');

        $saldosCajas = Caja::select('id_caja', 'nombre', 'saldo_inicial')
            ->get()
            ->map(function ($caja) use ($netos) {
                $caja->saldo_actual = round((float) $caja->saldo_inicial + (float) ($netos[$caja->id_caja] ?? 0), 2);

                return $caja;
            });

        $totalDeudores = Alumno::whereHas('matriculas', function ($q) {
            $q->whereHas('cuentasPorCobrar', function ($cq) {
                $cq->whereIn('estado', ['PENDIENTE', 'PARCIAL']);
            });
        })->distinct()->count();

        // PARCIAL también tiene saldo pendiente: excluirla subestimaba la
        // deuda frente a los reportes de deudores/vencidos.
        $totalVencidos = CuentaPorCobrar::where('fecha_vencimiento', '<', now())
            ->whereIn('estado', ['PENDIENTE', 'PARCIAL'])
            ->count();

        $pagosPorMetodo = $filtrarPagos(Pago::query())
            ->select('metodo_pago', DB::raw('count(*) as total, sum(monto_total) as monto_total'))
            ->groupBy('metodo_pago')
            ->get();

        $ingresosMensuales = $filtrarPagos(Pago::query())
            ->whereYear('fecha_pago', now()->year)
            ->get(['fecha_pago', 'monto_total'])
            ->groupBy(fn ($pago) => $pago->fecha_pago->month)
            ->map(fn ($pagos) => ['total' => $pagos->sum('monto_total')])
            ->toArray();

        $egresosMensuales = $filtrarGastos(Gasto::query())
            ->whereYear('fecha_gasto', now()->year)
            ->get(['fecha_gasto', 'monto'])
            ->groupBy(fn ($gasto) => $gasto->fecha_gasto->month)
            ->map(fn ($gastos) => ['total' => $gastos->sum('monto')])
            ->toArray();

        $data = [
            'total_alumnos' => Alumno::count(),
            'alumnos_activos' => Alumno::where('estado', 'ACTIVO')->count(),
            'total_apoderados' => Apoderado::count(),
            'matriculas_activas' => Matricula::where('estado', 'ACTIVA')->count(),
            'total_ingresos' => $totalIngresos,
            'total_egresos' => $totalEgresos,
            'pagos_hoy' => Pago::where('estado', 'CONFIRMADO')->whereDate('fecha_pago', today())->count(),
            'ingresos_hoy' => Pago::where('estado', 'CONFIRMADO')->whereDate('fecha_pago', today())->sum('monto_total'),
            'gastos_hoy' => Gasto::where('estado', 'REGISTRADO')->whereDate('fecha_gasto', today())->sum('monto'),
            'saldos_cajas' => $saldosCajas,
            'total_deudores' => $totalDeudores,
            'total_vencidos' => $totalVencidos,
            'ultimos_movimientos' => RegistroMovimiento::latest()->take(5)->get(),
            'pagos_por_metodo' => $pagosPorMetodo,
            'ingresos_mensuales' => $ingresosMensuales,
            'egresos_mensuales' => $egresosMensuales,
        ];

        return view('dashboard', $data);
    }
}
