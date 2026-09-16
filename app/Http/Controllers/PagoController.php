<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\PagoDetalle;
use App\Models\Matricula;
use App\Models\Caja;
use App\Models\CuentaPorCobrar;
use App\Models\ConceptoCobro;
use App\Models\MovimientoCaja;
use App\Models\ComprobantePago;
use App\Traits\RegistraMovimientos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PagoController extends Controller
{
    use RegistraMovimientos;

    public function index(Request $request)
    {
        $query = Pago::with(['matricula.alumno', 'caja']);

        // Filtro por caja
        if ($request->has('caja') && $request->caja != '') {
            $query->where('id_caja', $request->caja);
        }

        // Filtro por método de pago
        if ($request->has('metodo') && $request->metodo != '') {
            $query->where('metodo_pago', $request->metodo);
        }

        // Filtro por estado
        if ($request->has('estado') && $request->estado != '') {
            $query->where('estado', $request->estado);
        }

        // Filtro por fecha desde
        if ($request->has('fecha_desde') && $request->fecha_desde != '') {
            $query->whereDate('fecha_pago', '>=', $request->fecha_desde);
        }

        // Filtro por fecha hasta
        if ($request->has('fecha_hasta') && $request->fecha_hasta != '') {
            $query->whereDate('fecha_pago', '<=', $request->fecha_hasta);
        }

        // Búsqueda
        if ($request->has('busqueda') && $request->busqueda != '') {
            $busqueda = $request->busqueda;
            $query->where(function($q) use ($busqueda) {
                $q->where('codigo', 'LIKE', "%{$busqueda}%")
                  ->orWhere('numero_operacion', 'LIKE', "%{$busqueda}%")
                  ->orWhereHas('matricula.alumno', function($sub) use ($busqueda) {
                      $sub->where('nombres', 'LIKE', "%{$busqueda}%")
                          ->orWhere('apellidos', 'LIKE', "%{$busqueda}%")
                          ->orWhere('dni', 'LIKE', "%{$busqueda}%");
                  });
            });
        }

        $pagos = $query->orderBy('fecha_pago', 'desc')->paginate(20);

        $cajas = Caja::where('estado', 'ACTIVA')->get();

        // Estadísticas
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
        // Si viene con una matrícula preseleccionada
        $matriculaSeleccionada = null;
        if ($request->has('matricula') && $request->matricula != '') {
            $matriculaSeleccionada = Matricula::with(['alumno', 'cuentasPorCobrar'])
                                              ->find($request->matricula);
        }

        $cajas = Caja::where('estado', 'ACTIVA')->get();

        // Matrículas con cuentas pendientes
        $matriculas = Matricula::with(['alumno', 'cuentasPorCobrar.concepto'])
                               ->whereIn('estado', ['ACTIVA', 'PENDIENTE'])
                               ->whereHas('cuentasPorCobrar', function($q) {
                                   $q->whereIn('estado', ['PENDIENTE', 'PARCIAL']);
                               })
                               ->get();

        return view('pagos.create', compact('cajas', 'matriculas', 'matriculaSeleccionada'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo' => ['required', 'string', 'max:40', Rule::unique('pagos', 'codigo')],
            'id_matricula' => 'required|exists:matriculas,id_matricula',
            'id_caja' => 'required|exists:cajas,id_caja',
            'fecha_pago' => 'required|date',
            'metodo_pago' => 'required|in:EFECTIVO,YAPE,PLIN,TRANSFERENCIA,TARJETA,OTRO',
            'numero_operacion' => 'nullable|string|max:100',
            'monto_total' => 'required|numeric|min:0.01',
            'observaciones' => 'nullable|string|max:500',
            'cuentas' => 'required|array|min:1',
            'cuentas.*.id_cuenta' => 'required|exists:cuentas_por_cobrar,id_cuenta',
            'cuentas.*.monto' => 'required|numeric|min:0.01',
        ]);

        try {
            DB::beginTransaction();

            // Verificar que el monto total coincida con la suma de los detalles
            $sumaDetalles = collect($request->cuentas)->sum('monto');

            if (abs($sumaDetalles - $validated['monto_total']) > 0.01) {
                return back()->withErrors([
                    'monto_total' => 'El monto total no coincide con la suma de los detalles.'
                ])->withInput();
            }

            // Crear el pago
            $pago = Pago::create([
                'codigo' => $validated['codigo'],
                'id_matricula' => $validated['id_matricula'],
                'id_caja' => $validated['id_caja'],
                'fecha_pago' => $validated['fecha_pago'],
                'metodo_pago' => $validated['metodo_pago'],
                'numero_operacion' => $validated['numero_operacion'],
                'monto_total' => $validated['monto_total'],
                'estado' => 'CONFIRMADO',
                'observaciones' => $validated['observaciones'],
                'registrado_por' => auth()->id(),
            ]);

            // Crear los detalles del pago y actualizar cuentas
            foreach ($request->cuentas as $cuentaData) {
                $cuenta = CuentaPorCobrar::find($cuentaData['id_cuenta']);

                // Verificar que el monto no exceda el pendiente
                $montoPendiente = $cuenta->monto_pendiente;

                if ($cuentaData['monto'] > $montoPendiente + 0.01) {
                    throw new \Exception("El monto para la cuenta {$cuenta->referencia} excede el pendiente.");
                }

                PagoDetalle::create([
                    'id_pago' => $pago->id_pago,
                    'id_cuenta' => $cuentaData['id_cuenta'],
                    'monto_aplicado' => $cuentaData['monto'],
                ]);

                // Actualizar estado de la cuenta
                $nuevoMontoPagado = $cuenta->pagoDetalles()->sum('monto_aplicado');
                $montoTotal = $cuenta->monto_original - $cuenta->descuento + $cuenta->recargo;

                if ($nuevoMontoPagado >= $montoTotal - 0.01) {
                    $cuenta->estado = 'PAGADA';
                } else {
                    $cuenta->estado = 'PARCIAL';
                }
                $cuenta->save();
            }

            // Crear movimiento de caja
            MovimientoCaja::create([
                'id_caja' => $validated['id_caja'],
                'tipo' => 'INGRESO',
                'origen' => 'PAGO_ALUMNO',
                'id_pago' => $pago->id_pago,
                'fecha_movimiento' => now(),
                'monto' => $validated['monto_total'],
                'descripcion' => "Pago {$pago->codigo} - " . $pago->matricula->alumno->nombres . ' ' . $pago->matricula->alumno->apellidos,
                'estado' => 'ACTIVO',
                'registrado_por' => auth()->id(),
            ]);

            // Generar comprobante
            ComprobantePago::create([
                'id_pago' => $pago->id_pago,
                'tipo' => 'RECIBO_INTERNO',
                'serie' => 'R001',
                'numero' => str_pad($pago->id_pago, 6, '0', STR_PAD_LEFT),
                'fecha_emision' => now(),
            ]);

            DB::commit();

            self::registrarMovimiento(
                'CREAR',
                'Pagos',
                'Pago',
                $pago->id_pago,
                "Registró el pago {$pago->codigo} por S/. {$pago->monto_total}",
                null,
                $pago->toArray()
            );

            return redirect()->route('pagos.show', $pago->id_pago)
                            ->with('success', '¡Pago registrado exitosamente!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
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

    public function anular(Request $request, Pago $pago)
    {
        $request->validate([
            'motivo_anulacion' => 'required|string|max:255',
        ]);

        if ($pago->estado === 'ANULADO') {
            return back()->withErrors(['error' => 'Este pago ya está anulado.']);
        }

        try {
            DB::beginTransaction();

            $pago->estado = 'ANULADO';
            $pago->anulado_por = auth()->id();
            $pago->anulado_at = now();
            $pago->motivo_anulacion = $request->motivo_anulacion;
            $pago->save();

            // Revertir las cuentas por cobrar
            foreach ($pago->detalles as $detalle) {
                $cuenta = $detalle->cuentaPorCobrar;
                if ($cuenta) {
                    $nuevoMontoPagado = $cuenta->pagoDetalles()
                                              ->whereHas('pago', function($q) {
                                                  $q->where('estado', 'CONFIRMADO');
                                              })
                                              ->sum('monto_aplicado');

                    $montoTotal = $cuenta->monto_original - $cuenta->descuento + $cuenta->recargo;

                    if ($nuevoMontoPagado >= $montoTotal - 0.01) {
                        $cuenta->estado = 'PAGADA';
                    } elseif ($nuevoMontoPagado > 0) {
                        $cuenta->estado = 'PARCIAL';
                    } else {
                        $cuenta->estado = 'PENDIENTE';
                    }
                    $cuenta->save();
                }
            }

            // Anular movimiento de caja
            MovimientoCaja::where('id_pago', $pago->id_pago)
                          ->update(['estado' => 'ANULADO']);

            // Anular comprobante
            if ($pago->comprobante) {
                $pago->comprobante->delete();
            }

            DB::commit();

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

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy(Pago $pago)
    {
        return back()->withErrors(['error' => 'Para eliminar un pago, primero debe anularlo.']);
    }
}
