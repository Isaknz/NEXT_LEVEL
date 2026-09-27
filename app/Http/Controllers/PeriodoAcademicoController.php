<?php

namespace App\Http\Controllers;

use App\Http\Requests\PeriodoRequest;
use App\Models\PeriodoAcademico;
use App\Traits\RegistraMovimientos;
use Illuminate\Support\Facades\DB;

class PeriodoAcademicoController extends Controller
{
    use RegistraMovimientos;

    public function index()
    {
        $periodos = PeriodoAcademico::withCount(['matriculas', 'ciclos'])
            ->orderByDesc('anio')
            ->orderBy('fecha_inicio')
            ->paginate(15);

        return view('periodos.index', compact('periodos'));
    }

    public function create()
    {
        return view('periodos.create');
    }

    public function store(PeriodoRequest $request)
    {
        $periodo = PeriodoAcademico::create($request->validated());

        $this->registrarMovimiento('CREAR', 'periodos', 'Periodo', $periodo->id_periodo, "Período creado: {$periodo->codigo}", null, $periodo->only(['codigo', 'nombre', 'anio', 'fecha_inicio', 'fecha_fin', 'estado']));

        return redirect()->route('periodos.index')->with('success', 'Período creado correctamente.');
    }

    public function edit(PeriodoAcademico $periodo)
    {
        return view('periodos.edit', compact('periodo'));
    }

    public function update(PeriodoRequest $request, PeriodoAcademico $periodo)
    {
        $anteriores = $periodo->only(['codigo', 'nombre', 'anio', 'fecha_inicio', 'fecha_fin', 'estado']);

        $periodo->update($request->validated());

        $this->registrarMovimiento('ACTUALIZAR', 'periodos', 'Periodo', $periodo->id_periodo, "Período actualizado: {$periodo->codigo}", $anteriores, $periodo->only(['codigo', 'nombre', 'anio', 'fecha_inicio', 'fecha_fin', 'estado']));

        return redirect()->route('periodos.index')->with('success', 'Período actualizado correctamente.');
    }

    public function destroy(PeriodoAcademico $periodo)
    {
        if ($periodo->estado === 'CERRADO') {
            return back()->with('error', "No se puede eliminar el período \"{$periodo->codigo}\" porque está CERRADO.");
        }

        $matriculas = $periodo->matriculas()->count();
        $ciclos = $periodo->ciclos()->count();

        if ($matriculas > 0 || $ciclos > 0) {
            return back()->with('error', "No se puede eliminar el período \"{$periodo->codigo}\": tiene {$matriculas} matrícula(s) y {$ciclos} ciclo(s) asociados. Ciérralo en su lugar.");
        }

        $codigo = $periodo->codigo;
        $id = $periodo->id_periodo;

        DB::transaction(fn () => $periodo->delete());

        $this->registrarMovimiento('ELIMINAR', 'periodos', 'Periodo', $id, "Período eliminado: {$codigo}");

        return redirect()->route('periodos.index')->with('success', 'Período eliminado correctamente.');
    }
}
