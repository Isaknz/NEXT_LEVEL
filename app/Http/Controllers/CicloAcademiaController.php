<?php

namespace App\Http\Controllers;

use App\Http\Requests\CicloRequest;
use App\Models\CicloAcademia;
use App\Models\Facultad;
use App\Models\PeriodoAcademico;
use App\Traits\RegistraMovimientos;
use Illuminate\Support\Facades\DB;

class CicloAcademiaController extends Controller
{
    use RegistraMovimientos;

    public function index()
    {
        $ciclos = CicloAcademia::with(['periodo', 'facultad'])
            ->withCount('matriculas')
            ->latest('id_ciclo')
            ->paginate(15);

        return view('ciclos.index', compact('ciclos'));
    }

    public function create()
    {
        $periodos = PeriodoAcademico::whereIn('estado', ['PLANIFICADO', 'ABIERTO'])->orderByDesc('anio')->get();
        $facultades = Facultad::where('estado', 'ACTIVO')->orderBy('nombre')->get();

        return view('ciclos.create', compact('periodos', 'facultades'));
    }

    public function store(CicloRequest $request)
    {
        $ciclo = CicloAcademia::create($request->validated());

        $this->registrarMovimiento('CREAR', 'ciclos', 'Ciclo', $ciclo->id_ciclo, "Ciclo creado: {$ciclo->nombre}", null, $ciclo->only(['id_periodo', 'id_facultad', 'nombre', 'turno', 'fecha_inicio', 'fecha_fin', 'monto_referencial', 'vacantes', 'estado']));

        return redirect()->route('ciclos.index')->with('success', 'Ciclo creado correctamente.');
    }

    public function edit(CicloAcademia $ciclo)
    {
        $periodos = PeriodoAcademico::whereIn('estado', ['PLANIFICADO', 'ABIERTO'])
            ->orWhere('id_periodo', $ciclo->id_periodo)
            ->orderByDesc('anio')
            ->get();

        $facultades = Facultad::where('estado', 'ACTIVO')
            ->orWhere('id_facultad', $ciclo->id_facultad)
            ->orderBy('nombre')
            ->get();

        return view('ciclos.edit', compact('ciclo', 'periodos', 'facultades'));
    }

    public function update(CicloRequest $request, CicloAcademia $ciclo)
    {
        $anteriores = $ciclo->only(['id_periodo', 'id_facultad', 'nombre', 'turno', 'fecha_inicio', 'fecha_fin', 'monto_referencial', 'vacantes', 'estado']);

        $ciclo->update($request->validated());

        $this->registrarMovimiento('ACTUALIZAR', 'ciclos', 'Ciclo', $ciclo->id_ciclo, "Ciclo actualizado: {$ciclo->nombre}", $anteriores, $ciclo->only(['id_periodo', 'id_facultad', 'nombre', 'turno', 'fecha_inicio', 'fecha_fin', 'monto_referencial', 'vacantes', 'estado']));

        return redirect()->route('ciclos.index')->with('success', 'Ciclo actualizado correctamente.');
    }

    public function destroy(CicloAcademia $ciclo)
    {
        if ($ciclo->estado === 'CERRADO') {
            return back()->with('error', "No se puede eliminar el ciclo \"{$ciclo->nombre}\" porque está CERRADO.");
        }

        $matriculas = $ciclo->matriculas()->count();

        if ($matriculas > 0) {
            return back()->with('error', "No se puede eliminar el ciclo \"{$ciclo->nombre}\": tiene {$matriculas} matrícula(s) asociadas. Cancélalo en su lugar.");
        }

        $nombre = $ciclo->nombre;
        $id = $ciclo->id_ciclo;

        DB::transaction(fn () => $ciclo->delete());

        $this->registrarMovimiento('ELIMINAR', 'ciclos', 'Ciclo', $id, "Ciclo eliminado: {$nombre}");

        return redirect()->route('ciclos.index')->with('success', 'Ciclo eliminado correctamente.');
    }
}
