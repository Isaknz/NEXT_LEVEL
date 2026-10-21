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
        $desde = $request->query('desde');
        $hasta = $request->query('hasta');
        $periodo = $request->query('periodo');
        $caja = $request->query('caja');

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

        $saldosCajas = Caja::select('id_caja', 'nombre', 'saldo_inicial')
            ->withCount(['movimientos as total_movimientos'])
            ->get()
            ->map(function ($caja) {
                $latestMovimiento = MovimientoCaja::where('id_caja', $caja->id_caja)
                    ->orderBy('fecha_movimiento', 'desc')
                    ->first();
                $saldo = $caja->saldo_inicial;
                if ($latestMovimiento) {
                    $movimientos = MovimientoCaja::where('id_caja', $caja->id_caja)
                        ->where('estado', 'ACTIVO')
                        ->get();
                    $saldo = $movimientos->sum(function ($m) {
                        return $m->tipo === 'INGRESO' ? $m->monto : -$m->monto;
                    });
                    $saldo += $caja->saldo_inicial;
                }
                $caja->saldo_actual = round($saldo, 2);

                return $caja;
            });

        $totalDeudores = Alumno::whereHas('matriculas', function ($q) {
            $q->whereHas('cuentasPorCobrar', function ($cq) {
                $cq->whereIn('estado', ['PENDIENTE', 'PARCIAL']);
            });
        })->distinct()->count();

        $totalVencidos = CuentaPorCobrar::where('fecha_vencimiento', '<', now())
            ->where('estado', 'PENDIENTE')
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
            'alumnos_retirados' => Alumno::where('estado', 'INACTIVO')->count(),
            'total_apoderados' => Apoderado::count(),
            'matriculas_activas' => Matricula::where('estado', 'ACTIVA')->count(),
            'matriculas_inactivas' => Matricula::where('estado', 'RETIRADA')->orWhere('estado', 'ANULADA')->orWhere('estado', 'FINALIZADA')->count(),
            'total_ingresos' => $totalIngresos,
            'total_egresos' => $totalEgresos,
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
