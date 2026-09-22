<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use App\Models\MovimientoCaja;
use Illuminate\Http\Request;

class CajaController extends Controller
{
    public function index() { return view('cajas.index'); }
    public function create() {
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        return view('cajas.create');
    }
    public function store(Request $request) { return redirect()->back(); }
    public function show(Caja $caja) { return view('cajas.show', compact('caja')); }
    public function edit(Caja $caja) {
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        return view('cajas.edit', compact('caja'));
    }
    public function update(Request $request, Caja $caja) {
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        return redirect()->back();
    }
    public function destroy(Caja $caja) {
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        return redirect()->back();
    }

    public function cerrar($id)
    {
        $caja = Caja::findOrFail($id);
        if ($caja->estado === 'CERRADA') {
            return redirect()->back()->withErrors(['caja' => 'La caja ya está cerrada']);
        }
        $ingresos = MovimientoCaja::where('caja_id', $id)->where('tipo', 'INGRESO')->sum('monto');
        $egresos = MovimientoCaja::where('caja_id', $id)->where('tipo', 'EGRESO')->sum('monto');
        $caja->update([
            'monto_apertura' => $caja->saldo_inicial,
            'monto_cierre' => $caja->saldo_inicial + $ingresos - $egresos,
            'fecha_cierre' => now(),
            'estado' => 'CERRADA',
            'usuarios_id_cerrado' => auth()->id(),
        ]);

        return redirect()->route('cajas.index')->with('status', 'Caja cerrada correctamente');
    }

    public function aperturar($id)
    {
        $caja = Caja::findOrFail($id);
        if ($caja->estado !== 'CERRADA') {
            return redirect()->back()->withErrors(['caja' => 'La caja no está cerrada']);
        }
        $caja->update([
            'estado' => 'ACTIVA',
            'fecha_cierre' => null,
            'usuarios_id_cerrado' => null,
        ]);

        return redirect()->route('cajas.index')->with('status', 'Caja reabierta correctamente');
    }

    public function ajuste(Request $request, $id)
    {
        $request->validate([
            'tipo' => 'required|in:INGRESO,EGRESO',
            'monto' => 'required|numeric|min:0.01',
            'descripcion' => 'nullable|string|max:500',
            'concepto' => 'nullable|string|max:255',
        ]);

        $caja = Caja::findOrFail($id);

        MovimientoCaja::create([
            'id_caja' => $id,
            'tipo' => $request->tipo,
            'origen' => 'AJUSTE',
            'monto' => $request->monto,
            'descripcion' => $request->descripcion,
            'concepto' => $request->concepto,
            'estado' => 'ACTIVO',
            'fecha_movimiento' => now(),
            'registrado_por' => auth()->id(),
        ]);

        return redirect()->route('cajas.index')->with('status', 'Ajuste registrado correctamente');
    }
}
