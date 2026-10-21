<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnulacionRequest;
use App\Http\Requests\PagoRequest;
use App\Models\Caja;
use App\Models\ComprobantePago;
use App\Models\CuentaPorCobrar;
use App\Models\Matricula;
use App\Models\MovimientoCaja;
use App\Models\Pago;
use App\Models\PagoDetalle;
use App\Traits\RegistraMovimientos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use PDF;

class PagoController extends Controller
{
    use RegistraMovimientos;

    public function index(Request $request)
    {
        $pagos = Pago::query()
            ->with(['matricula.alumno', 'caja'])
            ->filtrar($request)
            ->orderBy('fecha_pago', 'desc')
            ->paginate(20)
            ->withQueryString();

        $cajas = Caja::where('estado', 'ACTIVA')->get();

        $stats = [
            'total' => Pago::where('estado', 'CONFIRMADO')->count(),
            'total_mes' => Pago::where('estado', 'CONFIRMADO')
                ->whereMonth('fecha_pago', now()->month)
                ->whereYear('fecha_pago', now()->year)
                ->sum('monto_total'),
            'total_hoy' => Pago::where('estado', 'CONFIRMADO')
                ->whereDate('fecha_pago', today())
                ->sum('monto_total'),
            'anulados' => Pago::where('estado', 'ANULADO')->count(),
        ];

        return view('pagos.index', compact('pagos', 'cajas', 'stats'));
    }

    public function create(Request $request)
    {
        if (auth()->user()->role === 'cajero') {
            $cajas = Caja::where('estado', 'ACTIVA')->get();

            if ($cajas->isEmpty()) {
                return redirect()->route('cajas.index')
                    ->with('error', 'No hay ninguna caja abierta. Pide al administrador que aperture una.');
            }
        }

        $matriculaSeleccionada = null;
        if ($request->has('matricula') && $request->matricula != '') {
            $matriculaSeleccionada = Matricula::with(['alumno', 'cuentasPorCobrar'])
                ->find($request->matricula);
        }

        $cajas = Caja::where('estado', 'ACTIVA')->get();

        $matriculas = Matricula::with(['alumno', 'cuentasPorCobrar' => function ($q) {
            $q->whereIn('estado', ['PENDIENTE', 'PARCIAL']);
        }, 'cuentasPorCobrar.concepto'])
            ->whereIn('estado', ['ACTIVA', 'PENDIENTE'])
            ->whereHas('cuentasPorCobrar', function ($q) {
                $q->whereIn('estado', ['PENDIENTE', 'PARCIAL']);
            })
            ->get();

        return view('pagos.create', compact('cajas', 'matriculas', 'matriculaSeleccionada'));
    }

    public function store(PagoRequest $request)
    {
        $datos = $request->validated();

        $sumaDetalles = collect($datos['cuentas'])->sum('monto');

        if (abs($sumaDetalles - $datos['monto_total']) > 0.01) {
            throw ValidationException::withMessages([
                'monto_total' => 'El monto total no coincide con la suma de los detalles.',
            ]);
        }

        foreach ($datos['cuentas'] as $cuentaData) {
            $cuenta = CuentaPorCobrar::find($cuentaData['id_cuenta']);

            if (! $cuenta || $cuenta->id_matricula != $datos['id_matricula']) {
                throw ValidationException::withMessages([
                    'cuentas' => 'Una de las cuentas seleccionadas no pertenece a la matrícula elegida.',
                ]);
            }

            if (! in_array($cuenta->estado, ['PENDIENTE', 'PARCIAL'], true)) {
                throw ValidationException::withMessages([
                    'cuentas' => "La cuenta {$cuenta->referencia} ya está {$cuenta->estado} y no admite pagos.",
                ]);
            }

            if ($cuentaData['monto'] > $cuenta->monto_pendiente + 0.01) {
                throw ValidationException::withMessages([
                    'cuentas' => "El monto para la cuenta {$cuenta->referencia} excede el saldo pendiente.",
                ]);
            }
        }

        // La caja debe estar abierta: un pago contra una caja cerrada deja
        // el arqueo de cierre desactualizado.
        $caja = Caja::find($datos['id_caja']);

        if (! $caja || $caja->estado !== 'ACTIVA') {
            throw ValidationException::withMessages([
                'id_caja' => 'La caja seleccionada no está abierta.',
            ]);
        }

        $pago = null;

        try {
            DB::transaction(function () use ($datos, &$pago) {
                $pago = Pago::create([
                    'codigo' => $datos['codigo'],
                    'id_matricula' => $datos['id_matricula'],
                    'id_caja' => $datos['id_caja'],
                    'fecha_pago' => $datos['fecha_pago'],
                    'metodo_pago' => $datos['metodo_pago'],
                    'numero_operacion' => $datos['numero_operacion'] ?? null,
                    'monto_total' => $datos['monto_total'],
                    'estado' => 'CONFIRMADO',
                    'observaciones' => $datos['observaciones'] ?? null,
                    'registrado_por' => auth()->id(),
                ]);

                foreach ($datos['cuentas'] as $cuentaData) {
                    // lockForUpdate evita que dos pagos simultáneos lean el
                    // mismo saldo y sobrepasen la cuenta.
                    $cuenta = CuentaPorCobrar::lockForUpdate()->find($cuentaData['id_cuenta']);

                    if ($cuentaData['monto'] > $cuenta->monto_pendiente + 0.01) {
                        throw ValidationException::withMessages([
                            'cuentas' => "El saldo de la cuenta {$cuenta->referencia} cambió. Vuelve a intentarlo.",
                        ]);
                    }

                    PagoDetalle::create([
                        'id_pago' => $pago->id_pago,
                        'id_cuenta' => $cuentaData['id_cuenta'],
                        'monto_aplicado' => $cuentaData['monto'],
                    ]);

                    $cuenta->recalcularEstado();
                }

                MovimientoCaja::create([
                    'id_caja' => $datos['id_caja'],
                    'tipo' => 'INGRESO',
                    'origen' => 'PAGO_ALUMNO',
                    'id_pago' => $pago->id_pago,
                    'fecha_movimiento' => now(),
                    'monto' => $datos['monto_total'],
                    'descripcion' => "Pago {$pago->codigo} - ".($pago->matricula->alumno?->nombres ?? 'N/A').' '.($pago->matricula->alumno?->apellidos ?? ''),
                    'estado' => 'ACTIVO',
                    'registrado_por' => auth()->id(),
                ]);

                ComprobantePago::create([
                    'id_pago' => $pago->id_pago,
                    'tipo' => 'RECIBO_INTERNO',
                    'serie' => 'R001',
                    'numero' => str_pad($pago->id_pago, 6, '0', STR_PAD_LEFT),
                    'fecha_emision' => now(),
                ]);

                // La matrícula queda PAGADA cuando no quedan cuentas pendientes
                // ni parciales. No se pisa un estado terminal (ANULADA/RETIRADA).
                $quedanSaldo = $pago->matricula->cuentasPorCobrar()
                    ->whereIn('estado', ['PENDIENTE', 'PARCIAL'])
                    ->exists();

                if (! $quedanSaldo && in_array($pago->matricula->estado, ['ACTIVA', 'PENDIENTE'], true)) {
                    $pago->matricula->update(['estado' => 'PAGADA']);
                }
            });
        } catch (\Throwable $e) {
            Log::error('Error al registrar pago', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'datos' => $datos,
            ]);

            return back()->withErrors([
                'error' => 'Ocurrió un error al registrar el pago. Inténtalo nuevamente.',
            ])->withInput();
        }

        self::registrarMovimiento(
            'CREAR',
            'Pagos',
            'Pago',
            $pago->id_pago,
            "Registró el pago {$pago->codigo} por S/. {$pago->monto_total}",
            null,
            $pago->toArray()
        );

        // El cajero no tiene acceso a pagos.index ni pagos.show; se le
        // devuelve al formulario para que pueda seguir cobrando.
        $destino = auth()->user()->role === 'cajero'
            ? redirect()->route('pagos.create')->with('success', "Pago {$pago->codigo} registrado exitosamente.")
            : redirect()->route('pagos.show', $pago->id_pago)->with('success', '¡Pago registrado exitosamente!');

        return $destino;
    }

    public function show(Pago $pago)
    {
        $pago->load([
            'matricula.alumno',
            'matricula.periodo',
            'matricula.nivel',
            'matricula.grado',
            'caja',
            'detalles.cuentaPorCobrar.concepto',
            'comprobante',
            'registradoPor',
        ]);

        return view('pagos.show', compact('pago'));
    }

    public function comprobante(Pago $pago)
    {
        $pago->load(['matricula.alumno', 'matricula.nivel', 'matricula.alumno.apoderado', 'detalles.cuentaPorCobrar.concepto']);
        $pdf = PDF::loadView('pagos.comprobante', compact('pago'));

        return $pdf->stream('comprobante-pago-'.$pago->codigo.'.pdf');
    }

    public function anular(AnulacionRequest $request, Pago $pago)
    {
        if (auth()->user()->role === 'secretaria') {
            return abort(403);
        }
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }

        if ($request->user()->cannot('anular', $pago)) {
            return back()->withErrors(['error' => 'No tienes permiso para anular pagos.']);
        }

        if ($pago->estado === 'ANULADO') {
            return back()->withErrors(['error' => 'Este pago ya está anulado.']);
        }

        try {
            DB::transaction(function () use ($request, $pago) {
                $pago->estado = 'ANULADO';
                $pago->anulado_por = auth()->id();
                $pago->anulado_at = now();
                $pago->motivo_anulacion = $request->motivo_anulacion;
                $pago->save();

                foreach ($pago->detalles as $detalle) {
                    $detalle->cuentaPorCobrar?->recalcularEstado();
                }

                // Si al quitar este pago vuelven a quedar cuentas por cobrar,
                // la matrícula deja de estar PAGADA.
                if ($pago->matricula->estado === 'PAGADA') {
                    $quedanSaldo = $pago->matricula->cuentasPorCobrar()
                        ->whereIn('estado', ['PENDIENTE', 'PARCIAL'])
                        ->exists();

                    if ($quedanSaldo) {
                        $pago->matricula->update(['estado' => 'ACTIVA']);
                    }
                }

                MovimientoCaja::where('id_pago', $pago->id_pago)
                    ->update(['estado' => 'ANULADO']);

                if ($pago->comprobante) {
                    $pago->comprobante->delete();
                }
            });
        } catch (\Throwable $e) {
            Log::error('Error al anular pago', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'id_pago' => $pago->id_pago,
            ]);

            return back()->withErrors([
                'error' => 'Ocurrió un error al anular el pago. Inténtalo nuevamente.',
            ]);
        }

        self::registrarMovimiento(
            'ANULAR',
            'Pagos',
            'Pago',
            $pago->id_pago,
            "Anuló el pago {$pago->codigo}. Motivo: {$request->motivo_anulacion}",
            ['estado' => 'CONFIRMADO'],
            ['estado' => 'ANULADO']
        );

        return redirect()->route('pagos.index')
            ->with('success', '¡Pago anulado exitosamente!');
    }

    public function destroy(Pago $pago)
    {
        if (auth()->user()->role === 'secretaria') {
            return abort(403, 'No puedes anular pagos');
        }
        if (auth()->user()->role === 'cajero') {
            return back()->withErrors(['error' => 'Sin permisos para eliminar pagos']);
        }

        return back()->withErrors(['error' => 'Para eliminar un pago, primero debe anularlo.']);
    }
}
