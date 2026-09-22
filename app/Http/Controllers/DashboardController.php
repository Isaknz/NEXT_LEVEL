<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Apoderado;
use App\Models\Matricula;
use App\Models\Pago;
use App\Models\Gasto;
use App\Models\Caja;
use App\Models\CuentaPorCobrar;
use App\Models\RegistroMovimiento;
use App\Models\MovimientoCaja;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalIngresos = Pago::where('estado', 'CONFIRMADO')->sum('monto_total');
        $totalEgresos = Gasto::where('estado', 'REGISTRADO')->sum('monto');

        $saldosCajas = Caja::select('id', 'nombre', 'saldo_inicial')
            ->withCount(['movimientos as total_movimientos'])
            ->get()
            ->map(function ($caja) {
                $latestMovimiento = MovimientoCaja::where('id_caja', $caja->id)
                    ->orderBy('fecha_movimiento', 'desc')
                    ->first();
                $saldo = $caja->saldo_inicial;
                if ($latestMovimiento) {
                    $movimientos = MovimientoCaja::where('id_caja', $caja->id)->get();
                    $saldo = $movimientos->sum(function ($m) {
                        return $m->tipo === 'INGRESO' ? $m->monto : -$m->monto;
                    });
                    $saldo += $caja->saldo_inicial;
                }
                return [
                    'id' => $caja->id,
                    'nombre' => $caja->nombre,
                    'saldo_inicial' => $caja->saldo_inicial,
                    'saldo_actual' => round($saldo, 2),
                ];
            });

        $totalDeudores = Alumno::whereHas('matriculas', function ($q) {
            $q->whereHas('cuentasPorCobrar', function ($cq) {
                $cq->whereIn('estado', ['PENDIENTE', 'PARCIAL']);
            });
        })->distinct()->count();

        $totalVencidos = CuentaPorCobrar::where('fecha_vencimiento', '<', now())
            ->where('estado', 'PENDIENTE')
            ->count();

        $pagosPorMetodo = Pago::where('estado', 'CONFIRMADO')
            ->select('metodo_pago', DB::raw('count(*) as total, sum(monto_total) as monto_total'))
            ->groupBy('metodo_pago')
            ->get();

        $ingresosMensuales = Pago::where('estado', 'CONFIRMADO')
            ->select(DB::raw('MONTH(fecha_pago) as mes, sum(monto_total) as total'))
            ->whereYear('fecha_pago', now()->year)
            ->groupBy('mes')
            ->orderBy('mes')
            ->get()
            ->keyBy('mes')
            ->toArray();

        $egresosMensuales = Gasto::where('estado', 'REGISTRADO')
            ->select(DB::raw('MONTH(fecha_gasto) as mes, sum(monto) as total'))
            ->whereYear('fecha_gasto', now()->year)
            ->groupBy('mes')
            ->orderBy('mes')
            ->get()
            ->keyBy('mes')
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
