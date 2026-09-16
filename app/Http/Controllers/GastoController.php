<?php

namespace App\Http\Controllers;

use App\Models\Gasto;
use App\Models\CategoriaGasto;
use App\Models\Caja;
use App\Models\MovimientoCaja;
use App\Traits\RegistraMovimientos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class GastoController extends Controller
{
    use RegistraMovimientos;

    public function index(Request $request)
    {
        $query = Gasto::with(['categoria', 'caja', 'registradoPor']);

        // Filtro por caja
        if ($request->has('caja') && $request->caja != '') {
            $query->where('id_caja', $request->caja);
        }

        // Filtro por categoría
        if ($request->has('categoria') && $request->categoria != '') {
            $query->where('id_categoria_gasto', $request->categoria);
        }

        // Filtro por estado
        if ($request->has('estado') && $request->estado != '') {
            $query->where('estado', $request->estado);
        }

        // Filtro por fecha desde
        if ($request->has('fecha_desde') && $request->fecha_desde != '') {
            $query->whereDate('fecha_gasto', '>=', $request->fecha_desde);
        }

        // Filtro por fecha hasta
        if ($request->has('fecha_hasta') && $request->fecha_hasta != '') {
            $query->whereDate('fecha_gasto', '<=', $request->fecha_hasta);
        }

        // Búsqueda
        if ($request->has('busqueda') && $request->busqueda != '') {
            $busqueda = $request->busqueda;
            $query->where(function($q) use ($busqueda) {
                $q->where('codigo', 'LIKE', "%{$busqueda}%")
                  ->orWhere('concepto', 'LIKE', "%{$busqueda}%")
                  ->orWhere('proveedor', 'LIKE', "%{$busqueda}%")
                  ->orWhere('numero_comprobante', 'LIKE', "%{$busqueda}%");
            });
        }

        $gastos = $query->orderBy('fecha_gasto', 'desc')->paginate(20);

        $cajas = Caja::where('estado', 'ACTIVA')->get();
        $categorias = CategoriaGasto::where('estado', 'ACTIVO')->get();

        // Estadísticas
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo' => ['required', 'string', 'max:40', Rule::unique('gastos', 'codigo')],
            'id_categoria_gasto' => 'required|exists:categorias_gasto,id_categoria_gasto',
            'id_caja' => 'required|exists:cajas,id_caja',
            'fecha_gasto' => 'required|date',
            'proveedor' => 'nullable|string|max:150',
            'concepto' => 'required|string|max:150',
            'descripcion' => 'nullable|string|max:500',
            'monto' => 'required|numeric|min:0.01',
            'tipo_comprobante' => 'required|in:BOLETA,FACTURA,RECIBO,NOTA,SIN_COMPROBANTE',
            'serie_comprobante' => 'nullable|string|max:10',
            'numero_comprobante' => 'nullable|string|max:50',
            'archivo_url' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $validated['estado'] = 'REGISTRADO';
            $validated['registrado_por'] = auth()->id();

            $gasto = Gasto::create($validated);

            // Registrar movimiento de caja (EGRESO)
            MovimientoCaja::create([
                'id_caja' => $validated['id_caja'],
                'tipo' => 'EGRESO',
                'origen' => 'GASTO',
                'id_gasto' => $gasto->id_gasto,
                'fecha_movimiento' => now(),
                'monto' => $validated['monto'],
                'descripcion' => "Gasto {$gasto->codigo} - {$gasto->concepto}",
                'estado' => 'ACTIVO',
                'registrado_por' => auth()->id(),
            ]);

            DB::commit();

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

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function show(Gasto $gasto)
    {
        $gasto->load(['categoria', 'caja', 'registradoPor', 'anuladoPor']);
        return view('gastos.show', compact('gasto'));
    }

    public function edit(Gasto $gasto)
    {
        if ($gasto->estado === 'ANULADO') {
            return redirect()->route('gastos.index')
                            ->with('error', 'No se puede editar un gasto anulado.');
        }

        $cajas = Caja::where('estado', 'ACTIVA')->get();
        $categorias = CategoriaGasto::where('estado', 'ACTIVO')->orderBy('nombre')->get();

        return view('gastos.edit', compact('gasto', 'cajas', 'categorias'));
    }

    public function update(Request $request, Gasto $gasto)
    {
        if ($gasto->estado === 'ANULADO') {
            return redirect()->route('gastos.index')
                            ->with('error', 'No se puede editar un gasto anulado.');
        }

        $valoresAnteriores = $gasto->toArray();

        $validated = $request->validate([
            'codigo' => ['required', 'string', 'max:40', Rule::unique('gastos', 'codigo')->ignore($gasto->id_gasto, 'id_gasto')],
            'id_categoria_gasto' => 'required|exists:categorias_gasto,id_categoria_gasto',
            'id_caja' => 'required|exists:cajas,id_caja',
            'fecha_gasto' => 'required|date',
            'proveedor' => 'nullable|string|max:150',
            'concepto' => 'required|string|max:150',
            'descripcion' => 'nullable|string|max:500',
            'monto' => 'required|numeric|min:0.01',
            'tipo_comprobante' => 'required|in:BOLETA,FACTURA,RECIBO,NOTA,SIN_COMPROBANTE',
            'serie_comprobante' => 'nullable|string|max:10',
            'numero_comprobante' => 'nullable|string|max:50',
            'archivo_url' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $gasto->update($validated);

            // Actualizar el movimiento de caja asociado
            MovimientoCaja::where('id_gasto', $gasto->id_gasto)
                          ->update([
                              'id_caja' => $validated['id_caja'],
                              'monto' => $validated['monto'],
                              'descripcion' => "Gasto {$gasto->codigo} - {$gasto->concepto}",
                          ]);

            DB::commit();

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

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function anular(Request $request, Gasto $gasto)
    {
        $request->validate([
            'motivo_anulacion' => 'required|string|max:255',
        ]);

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

            // Anular movimiento de caja
            MovimientoCaja::where('id_gasto', $gasto->id_gasto)
                          ->update(['estado' => 'ANULADO']);

            DB::commit();

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

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy(Gasto $gasto)
    {
        return back()->withErrors(['error' => 'Para eliminar un gasto, primero debe anularlo.']);
    }
}
