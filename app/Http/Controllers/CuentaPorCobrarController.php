<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnulacionRequest;
use App\Http\Requests\CuentaPorCobrarRequest;
use App\Models\ConceptoCobro;
use App\Models\CuentaPorCobrar;
use App\Models\Matricula;
use App\Traits\RegistraMovimientos;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CuentaPorCobrarController extends Controller
{
    use RegistraMovimientos;

    public function index(Request $request)
    {
        $cuentas = CuentaPorCobrar::query()
            ->with(['matricula.alumno', 'concepto', 'pagoDetalles'])
            ->when($request->filled('estado'), fn (Builder $q) => $q->where('estado', $request->estado))
            ->when($request->filled('concepto'), fn (Builder $q) => $q->where('id_concepto', $request->concepto))
            ->when($request->filled('vencidas'), fn (Builder $q) => $q
                ->whereIn('estado', ['PENDIENTE', 'PARCIAL'])
                ->whereNotNull('fecha_vencimiento')
                ->whereDate('fecha_vencimiento', '<', now()))
            ->when($request->filled('busqueda'), function (Builder $q) use ($request) {
                $b = $request->busqueda;
                $q->where(function (Builder $inner) use ($b) {
                    $inner->where('referencia', 'LIKE', "%{$b}%")
                        ->orWhere('descripcion', 'LIKE', "%{$b}%")
                        ->orWhereHas('matricula.alumno', function (Builder $sub) use ($b) {
                            $sub->where('nombres', 'LIKE', "%{$b}%")
                                ->orWhere('apellidos', 'LIKE', "%{$b}%")
                                ->orWhere('dni', 'LIKE', "%{$b}%");
                        });
                });
            })
            ->orderByDesc('id_cuenta')
            ->paginate(15)
            ->withQueryString();

        $resumen = [
            'PENDIENTE' => CuentaPorCobrar::where('estado', 'PENDIENTE')->count(),
            'PARCIAL' => CuentaPorCobrar::where('estado', 'PARCIAL')->count(),
            'PAGADA' => CuentaPorCobrar::where('estado', 'PAGADA')->count(),
            'ANULADA' => CuentaPorCobrar::where('estado', 'ANULADA')->count(),
        ];

        $conceptos = ConceptoCobro::where('estado', 'ACTIVO')->orderBy('nombre')->get();

        return view('cuentas-por-cobrar.index', compact('cuentas', 'resumen', 'conceptos'));
    }

    public function create()
    {
        // 'PARCIAL' no existe en el enum de matriculas.estado: los estados
        // PENDIENTE y PARCIAL pertenecen a cuentas_por_cobrar.estado.
        $matriculas = Matricula::with('alumno')
            ->whereIn('estado', ['PENDIENTE', 'ACTIVA'])
            ->orderByDesc('id_matricula')
            ->limit(200)
            ->get();

        $conceptos = ConceptoCobro::where('estado', 'ACTIVO')->orderBy('nombre')->get();

        return view('cuentas-por-cobrar.create', compact('matriculas', 'conceptos'));
    }

    public function store(CuentaPorCobrarRequest $request)
    {
        $datos = $request->validated();

        $datos['estado'] = 'PENDIENTE';
        $datos['descuento'] = $datos['descuento'] ?? 0;
        $datos['recargo'] = $datos['recargo'] ?? 0;
        $datos['creado_por'] = auth()->id();
        $datos['updated_by'] = auth()->id();

        $cuenta = DB::transaction(fn () => CuentaPorCobrar::create($datos));

        $this->registrarMovimiento('CREAR', 'cuentas_por_cobrar', 'Cuenta', $cuenta->id_cuenta, "Cuenta por cobrar creada: {$cuenta->referencia} por " . number_format((float) $cuenta->monto_total, 2), null, $cuenta->only(['id_matricula', 'id_concepto', 'referencia', 'monto_original', 'descuento', 'recargo']));

        return redirect()->route('cuentas-por-cobrar.index')->with('success', 'Cuenta por cobrar creada correctamente.');
    }

    public function edit(CuentaPorCobrar $cuenta)
    {
        if ($cuenta->estado === 'ANULADA') {
            return back()->with('error', 'No se puede editar una cuenta anulada.');
        }

        if ($cuenta->pagoDetalles()->exists()) {
            return back()->with('error', "No se puede editar la cuenta \"{$cuenta->referencia}\" porque ya tiene pagos aplicados. Anúlela y cree una nueva.");
        }

        $matriculas = Matricula::with('alumno')
            ->where(function ($q) use ($cuenta) {
                $q->whereIn('estado', ['PENDIENTE', 'ACTIVA'])
                    ->orWhere('id_matricula', $cuenta->id_matricula);
            })
            ->orderByDesc('id_matricula')
            ->limit(200)
            ->get();

        $conceptos = ConceptoCobro::where(function ($q) use ($cuenta) {
            $q->where('estado', 'ACTIVO')
                ->orWhere('id_concepto', $cuenta->id_concepto);
        })
            ->orderBy('nombre')
            ->get();

        return view('cuentas-por-cobrar.edit', compact('cuenta', 'matriculas', 'conceptos'));
    }

    public function update(CuentaPorCobrarRequest $request, CuentaPorCobrar $cuenta)
    {
        if ($cuenta->estado === 'ANULADA') {
            return back()->with('error', 'No se puede editar una cuenta anulada.');
        }

        if ($cuenta->pagoDetalles()->exists()) {
            return back()->with('error', "No se puede editar la cuenta \"{$cuenta->referencia}\" porque ya tiene pagos aplicados. Anúlela y cree una nueva.");
        }

        $datos = $request->validated();
        $datos['updated_by'] = auth()->id();

        $anteriores = $cuenta->only(['id_matricula', 'id_concepto', 'referencia', 'monto_original', 'descuento', 'recargo', 'fecha_emision', 'fecha_vencimiento']);

        $cuenta->update($datos);

        $this->registrarMovimiento('ACTUALIZAR', 'cuentas_por_cobrar', 'Cuenta', $cuenta->id_cuenta, "Cuenta por cobrar actualizada: {$cuenta->referencia}", $anteriores, $datos);

        return redirect()->route('cuentas-por-cobrar.index')->with('success', 'Cuenta por cobrar actualizada correctamente.');
    }

    public function destroy(CuentaPorCobrar $cuenta)
    {
        if ($cuenta->pagoDetalles()->exists()) {
            return back()->with('error', "No se puede eliminar la cuenta \"{$cuenta->referencia}\" porque tiene pagos aplicados. Anúlela en su lugar.");
        }

        $referencia = $cuenta->referencia;
        $id = $cuenta->id_cuenta;

        DB::transaction(fn () => $cuenta->delete());

        $this->registrarMovimiento('ELIMINAR', 'cuentas_por_cobrar', 'Cuenta', $id, "Cuenta por cobrar eliminada: {$referencia}");

        return redirect()->route('cuentas-por-cobrar.index')->with('success', 'Cuenta por cobrar eliminada correctamente.');
    }

    public function anular(AnulacionRequest $request, CuentaPorCobrar $cuenta)
    {
        if ($cuenta->estado === 'ANULADA') {
            return back()->with('error', 'La cuenta ya está anulada.');
        }

        if ($cuenta->estado === 'PAGADA') {
            return back()->with('error', "No se puede anular la cuenta \"{$cuenta->referencia}\" porque está PAGADA. Anule primero el pago que la canceló.");
        }

        $anteriores = $cuenta->only(['estado']);

        $cuenta->update([
            'estado' => 'ANULADA',
            'anulado_at' => now(),
            'motivo_anulacion' => $request->motivo_anulacion,
            'updated_by' => auth()->id(),
        ]);

        $this->registrarMovimiento('ANULAR', 'cuentas_por_cobrar', 'Cuenta', $cuenta->id_cuenta, "Cuenta por cobrar anulada: {$cuenta->referencia} - {$request->motivo_anulacion}", $anteriores, ['estado' => 'ANULADA']);

        return redirect()->route('cuentas-por-cobrar.index')->with('success', 'Cuenta por cobrar anulada correctamente.');
    }
}
