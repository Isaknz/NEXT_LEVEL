<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConceptoCobroRequest;
use App\Models\ConceptoCobro;
use App\Traits\RegistraMovimientos;
use Illuminate\Support\Facades\DB;

class ConceptoCobroController extends Controller
{
    use RegistraMovimientos;

    public function index()
    {
        $conceptos = ConceptoCobro::withCount('cuentasPorCobrar')
            ->orderBy('codigo')
            ->paginate(15);

        return view('conceptos.index', compact('conceptos'));
    }

    public function create()
    {
        return view('conceptos.create');
    }

    public function store(ConceptoCobroRequest $request)
    {
        $concepto = ConceptoCobro::create($request->validated());

        $this->registrarMovimiento('CREAR', 'conceptos', 'Concepto', $concepto->id_concepto, "Concepto creado: {$concepto->nombre}", null, $concepto->only(['codigo', 'nombre', 'tipo', 'modalidad_aplicable', 'monto_referencial', 'estado']));

        return redirect()->route('conceptos.index')->with('success', 'Concepto creado correctamente.');
    }

    public function edit(ConceptoCobro $concepto)
    {
        return view('conceptos.edit', compact('concepto'));
    }

    public function update(ConceptoCobroRequest $request, ConceptoCobro $concepto)
    {
        $anteriores = $concepto->only(['codigo', 'nombre', 'tipo', 'modalidad_aplicable', 'monto_referencial', 'estado']);

        $concepto->update($request->validated());

        $this->registrarMovimiento('ACTUALIZAR', 'conceptos', 'Concepto', $concepto->id_concepto, "Concepto actualizado: {$concepto->nombre}", $anteriores, $concepto->only(['codigo', 'nombre', 'tipo', 'modalidad_aplicable', 'monto_referencial', 'estado']));

        return redirect()->route('conceptos.index')->with('success', 'Concepto actualizado correctamente.');
    }

    public function destroy(ConceptoCobro $concepto)
    {
        $cuentas = $concepto->cuentasPorCobrar()->count();

        if ($cuentas > 0) {
            return back()->with('error', "No se puede eliminar el concepto \"{$concepto->nombre}\": tiene {$cuentas} cuenta(s) por cobrar asociada(s). Desactívelo en su lugar.");
        }

        $nombre = $concepto->nombre;
        $id = $concepto->id_concepto;

        DB::transaction(fn () => $concepto->delete());

        $this->registrarMovimiento('ELIMINAR', 'conceptos', 'Concepto', $id, "Concepto eliminado: {$nombre}");

        return redirect()->route('conceptos.index')->with('success', 'Concepto eliminado correctamente.');
    }
}
