<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnulacionRequest;
use App\Http\Requests\GastoRequest;
use App\Models\Gasto;
use App\Models\CategoriaGasto;
use App\Models\Caja;
use App\Models\MovimientoCaja;
use App\Traits\RegistraMovimientos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GastoController extends Controller
{
    use RegistraMovimientos;

    public function index(Request $request)
    {
        $gastos = Gasto::query()
            ->with(['categoria', 'caja', 'registradoPor'])
            ->filtrar($request)
            ->orderBy('fecha_gasto', 'desc')
            ->paginate(20)
            ->withQueryString();

        $cajas = Caja::where('estado', 'ACTIVA')->get();
        $categorias = CategoriaGasto::where('estado', 'ACTIVO')->get();

        $stats = [
            'total' => Gasto::where('estado', 'REGISTRADO')->count(),
            'total_mes' => Gasto::where('estado', 'REGISTRADO')
                              ->whereMonth('fecha_gasto', now()->month)
                              ->whereYear('fecha_gasto', now()->year)
                              ->sum('monto'),
            'total_hoy' => Gasto::where('estado', 'REGISTRADO')
                              ->whereDate('fecha_gasto', today())
                              ->sum('monto'),
            'anulados' => Gasto::where('estado', 'ANULADO')->count(),
        ];

        return view('gastos.index', compact('gastos', 'cajas', 'categorias', 'stats'));
    }

    public function create()
    {
        $cajas = Caja::where('estado', 'ACTIVA')->get();
        $categorias = CategoriaGasto::where('estado', 'ACTIVO')->orderBy('nombre')->get();

        return view('gastos.create', compact('cajas', 'categorias'));
    }

    public function store(GastoRequest $request)
    {
        $datos = $request->validated();

        $datos['estado'] = 'REGISTRADO';
        $datos['registrado_por'] = auth()->id();
        $datos['archivo_url'] = $this->guardarComprobante($request);

        unset($datos['archivo']);

        $gasto = null;

        try {
            DB::transaction(function () use ($request, $datos, &$gasto) {
                $gasto = Gasto::create($datos);

                MovimientoCaja::create([
                    'id_caja' => $datos['id_caja'],
                    'tipo' => 'EGRESO',
                    'origen' => 'GASTO',
                    'id_gasto' => $gasto->id_gasto,
                    'fecha_movimiento' => now(),
                    'monto' => $datos['monto'],
                    'descripcion' => "Gasto {$gasto->codigo} - {$gasto->concepto}",
                    'estado' => 'ACTIVO',
                    'registrado_por' => auth()->id(),
                ]);
            });
        } catch (\Throwable $e) {
            if ($datos['archivo_url'] ?? false) {
                Storage::disk('public')->delete($datos['archivo_url']);
            }

            Log::error('Error al registrar gasto', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'datos' => $datos,
            ]);

            return back()->withErrors([
                'error' => 'Ocurrió un error al registrar el gasto. Inténtalo nuevamente.',
            ])->withInput();
        }

        self::registrarMovimiento(
            'CREAR',
            'Gastos',
            'Gasto',
            $gasto->id_gasto,
            "Registró el gasto {$gasto->codigo} por S/. {$gasto->monto}",
            null,
            $gasto->toArray()
        );

        return redirect()->route('gastos.show', $gasto->id_gasto)
                        ->with('success', '¡Gasto registrado exitosamente!');
    }

    public function show(Gasto $gasto)
    {
        $gasto->load(['categoria', 'caja', 'registradoPor', 'anuladoPor']);
        return view('gastos.show', compact('gasto'));
    }

    public function edit(Gasto $gasto)
    {
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        if ($gasto->estado === 'ANULADO') {
            return redirect()->route('gastos.index')
                            ->with('error', 'No se puede editar un gasto anulado.');
        }

        $cajas = Caja::where('estado', 'ACTIVA')->get();
        $categorias = CategoriaGasto::where('estado', 'ACTIVO')->orderBy('nombre')->get();

        return view('gastos.edit', compact('gasto', 'cajas', 'categorias'));
    }

    public function update(GastoRequest $request, Gasto $gasto)
    {
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        if ($gasto->estado === 'ANULADO') {
            return redirect()->route('gastos.index')
                            ->with('error', 'No se puede editar un gasto anulado.');
        }

        $valoresAnteriores = $gasto->toArray();

        $datos = $request->validated();

        if ($request->hasFile('archivo')) {
            if ($gasto->archivo_url) {
                Storage::disk('public')->delete($gasto->archivo_url);
            }
            $datos['archivo_url'] = $this->guardarComprobante($request);
        }

        unset($datos['archivo']);

        try {
            DB::transaction(function () use ($request, $gasto, $datos) {
                $gasto->update($datos);

                MovimientoCaja::where('id_gasto', $gasto->id_gasto)
                              ->update([
                                  'id_caja' => $datos['id_caja'],
                                  'monto' => $datos['monto'],
                                  'descripcion' => "Gasto {$gasto->codigo} - {$gasto->concepto}",
                              ]);
            });
        } catch (\Throwable $e) {
            Log::error('Error al actualizar gasto', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'id_gasto' => $gasto->id_gasto,
            ]);

            return back()->withErrors([
                'error' => 'Ocurrió un error al actualizar el gasto. Inténtalo nuevamente.',
            ])->withInput();
        }

        self::registrarMovimiento(
            'ACTUALIZAR',
            'Gastos',
            'Gasto',
            $gasto->id_gasto,
            "Actualizó el gasto {$gasto->codigo}",
            $valoresAnteriores,
            $gasto->fresh()->toArray()
        );

        return redirect()->route('gastos.index')
                        ->with('success', '¡Gasto actualizado exitosamente!');
    }

    public function anular(AnulacionRequest $request, Gasto $gasto)
    {
        if (auth()->user()->role === 'secretaria') {
            return abort(403);
        }
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }

        if ($request->user()->cannot('anular', $gasto)) {
            return back()->withErrors(['error' => 'No tienes permiso para anular gastos.']);
        }

        if ($gasto->estado === 'ANULADO') {
            return back()->withErrors(['error' => 'Este gasto ya está anulado.']);
        }

        try {
            DB::beginTransaction();

            $gasto->estado = 'ANULADO';
            $gasto->anulado_por = auth()->id();
            $gasto->anulado_at = now();
            $gasto->motivo_anulacion = $request->motivo_anulacion;
            $gasto->save();

            MovimientoCaja::where('id_gasto', $gasto->id_gasto)
                          ->update(['estado' => 'ANULADO']);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Error al anular gasto', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'id_gasto' => $gasto->id_gasto,
            ]);

            return back()->withErrors([
                'error' => 'Ocurrió un error al anular el gasto. Inténtalo nuevamente.',
            ]);
        }

        self::registrarMovimiento(
            'ANULAR',
            'Gastos',
            'Gasto',
            $gasto->id_gasto,
            "Anuló el gasto {$gasto->codigo}. Motivo: {$request->motivo_anulacion}",
            ['estado' => 'REGISTRADO'],
            ['estado' => 'ANULADO']
        );

        return redirect()->route('gastos.index')
                        ->with('success', '¡Gasto anulado exitosamente!');
    }

    public function destroy(Gasto $gasto)
    {
        if (auth()->user()->role === 'secretaria') {
            return abort(403);
        }
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        return back()->withErrors(['error' => 'Para eliminar un gasto, primero debe anularlo.']);
    }

    /**
     * Guarda el comprobante cargado en el almacenamiento público.
     */
    private function guardarComprobante(GastoRequest $request): ?string
    {
        if (! $request->hasFile('archivo')) {
            return null;
        }

        return $request->file('archivo')->store('comprobantes/gastos', 'public');
    }
}