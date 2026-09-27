<?php

namespace App\Http\Controllers;

use App\Http\Requests\CajaRequest;
use App\Models\Caja;
use App\Models\MovimientoCaja;
use App\Traits\RegistraMovimientos;
use Illuminate\Support\Facades\DB;

class CajaController extends Controller
{
    use RegistraMovimientos;

    public function index()
    {
        $cajas = Caja::withCount(['movimientos', 'pagos', 'gastos'])
            ->orderBy('codigo')
            ->paginate(15);

        return view('cajas.index', compact('cajas'));
    }

    public function create()
    {
        return view('cajas.create');
    }

    public function store(CajaRequest $request)
    {
        $datos = $request->validated();
        $datos['estado'] = 'ACTIVA';
        $datos['saldo_inicial'] = $datos['saldo_inicial'] ?? 0;

        $caja = Caja::create($datos);

        $this->registrarMovimiento('CREAR', 'cajas', 'Caja', $caja->id_caja, "Caja creada: {$caja->codigo}", null, $caja->only(['codigo', 'nombre', 'tipo', 'estado', 'saldo_inicial']));

        return redirect()->route('cajas.index')->with('success', 'Caja creada correctamente.');
    }

    public function show(Caja $caja)
    {
        $caja->loadCount(['movimientos', 'pagos', 'gastos']);

        $movimientos = $caja->movimientos()
            ->with('registradoPor')
            ->orderByDesc('fecha_movimiento')
            ->orderByDesc('id_movimiento')
            ->paginate(25);

        [$ingresos, $egresos] = $this->totales($caja);

        return view('cajas.show', compact('caja', 'movimientos', 'ingresos', 'egresos'));
    }

    public function edit(Caja $caja)
    {
        $caja->loadCount(['movimientos', 'pagos', 'gastos']);

        return view('cajas.edit', compact('caja'));
    }

    public function update(CajaRequest $request, Caja $caja)
    {
        $datos = $request->validated();

        if ($caja->estado === 'CERRADA') {
            return back()->with('error', "No se puede modificar la caja \"{$caja->codigo}\" porque está CERRADA. Reábrela primero.");
        }

        // El saldo inicial solo es editable mientras la caja no tenga movimientos.
        if ($caja->movimientos()->exists() && array_key_exists('saldo_inicial', $datos)) {
            unset($datos['saldo_inicial']);
        }

        $anteriores = $caja->only(['codigo', 'nombre', 'tipo', 'estado', 'saldo_inicial']);

        $caja->update($datos);

        $this->registrarMovimiento('ACTUALIZAR', 'cajas', 'Caja', $caja->id_caja, "Caja actualizada: {$caja->codigo}", $anteriores, $caja->only(['codigo', 'nombre', 'tipo', 'estado', 'saldo_inicial']));

        return redirect()->route('cajas.index')->with('success', 'Caja actualizada correctamente.');
    }

    public function destroy(Caja $caja)
    {
        $movimientos = $caja->movimientos()->count();
        $pagos = $caja->pagos()->count();
        $gastos = $caja->gastos()->count();

        if ($movimientos > 0 || $pagos > 0 || $gastos > 0) {
            return back()->with('error', "No se puede eliminar la caja \"{$caja->codigo}\": tiene {$movimientos} movimiento(s), {$pagos} pago(s) y {$gastos} gasto(s) registrados. Ciérrala en su lugar.");
        }

        $codigo = $caja->codigo;
        $id = $caja->id_caja;

        DB::transaction(fn () => $caja->delete());

        $this->registrarMovimiento('ELIMINAR', 'cajas', 'Caja', $id, "Caja eliminada: {$codigo}");

        return redirect()->route('cajas.index')->with('success', 'Caja eliminada correctamente.');
    }

    /**
     * Ingresos y egresos acumulados de la caja, incluyendo los ajustes manuales.
     * Los movimientos anulados no se contabilizan.
     */
    private function totales(Caja $caja): array
    {
        $movimientos = $caja->movimientos()->where('estado', 'ACTIVO');

        $ingresos = (clone $movimientos)->whereIn('tipo', ['INGRESO', 'AJUSTE_INGRESO'])->sum('monto');
        $egresos = (clone $movimientos)->whereIn('tipo', ['EGRESO', 'AJUSTE_EGRESO'])->sum('monto');

        return [(float) $ingresos, (float) $egresos];
    }

    public function cerrar(Caja $caja)
    {
        if ($caja->estado === 'CERRADA') {
            return back()->with('error', 'La caja ya está cerrada.');
        }

        [$ingresos, $egresos] = $this->totales($caja);

        $caja->update([
            'monto_apertura' => $caja->saldo_inicial,
            'monto_cierre' => $caja->saldo_inicial + $ingresos - $egresos,
            'fecha_cierre' => now(),
            'estado' => 'CERRADA',
            'usuarios_id_cerrado' => auth()->id(),
        ]);

        $this->registrarMovimiento('CERRAR_CAJA', 'cajas', 'Caja', $caja->id_caja, "Caja cerrada: {$caja->codigo} con saldo final " . number_format((float) $caja->monto_cierre, 2));

        return redirect()->route('cajas.index')->with('success', 'Caja cerrada correctamente.');
    }

    public function aperturar(Caja $caja)
    {
        if ($caja->estado !== 'CERRADA') {
            return back()->with('error', 'La caja no está cerrada.');
        }

        $anteriores = $caja->only(['estado', 'monto_cierre', 'fecha_cierre']);

        $caja->update([
            'estado' => 'ACTIVA',
            'fecha_cierre' => null,
            'usuarios_id_cerrado' => null,
        ]);

        $this->registrarMovimiento('REABRIR_CAJA', 'cajas', 'Caja', $caja->id_caja, "Caja reabierta: {$caja->codigo}", $anteriores, $caja->only(['estado']));

        return redirect()->route('cajas.index')->with('success', 'Caja reabierta correctamente.');
    }

    public function formAjuste(Caja $caja)
    {
        if ($caja->estado === 'CERRADA') {
            return back()->with('error', 'No se pueden registrar ajustes en una caja cerrada.');
        }

        return view('cajas.ajuste', compact('caja'));
    }

    public function ajuste(\Illuminate\Http\Request $request, Caja $caja)
    {
        if ($caja->estado === 'CERRADA') {
            return back()->with('error', 'No se pueden registrar ajustes en una caja cerrada.');
        }

        $request->validate([
            'tipo' => 'required|in:INGRESO,EGRESO',
            'monto' => 'required|numeric|min:0.01|max:99999999.99',
            'descripcion' => 'nullable|string|max:500',
            'concepto' => 'nullable|string|max:255',
        ]);

        MovimientoCaja::create([
            'id_caja' => $caja->id_caja,
            'tipo' => $request->tipo === 'INGRESO' ? 'AJUSTE_INGRESO' : 'AJUSTE_EGRESO',
            'origen' => 'AJUSTE',
            'monto' => $request->monto,
            'descripcion' => $request->descripcion,
            'concepto' => $request->concepto,
            'estado' => 'ACTIVO',
            'fecha_movimiento' => now(),
            'registrado_por' => auth()->id(),
        ]);

        $this->registrarMovimiento('AJUSTE_CAJA', 'cajas', 'Caja', $caja->id_caja, "Ajuste {$request->tipo} por " . number_format((float) $request->monto, 2) . " en caja {$caja->codigo}");

        return redirect()->route('cajas.show', $caja)->with('success', 'Ajuste registrado correctamente.');
    }
}
