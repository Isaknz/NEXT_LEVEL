<?php

namespace App\Http\Controllers;

use App\Http\Requests\NivelRequest;
use App\Models\Nivel;
use App\Traits\RegistraMovimientos;
use Illuminate\Support\Facades\DB;

class NivelController extends Controller
{
    use RegistraMovimientos;

    public function index()
    {
        $niveles = Nivel::withCount('grados')
            ->orderBy('nombre')
            ->paginate(15);

        return view('niveles.index', compact('niveles'));
    }

    public function create()
    {
        return view('niveles.create');
    }

    public function store(NivelRequest $request)
    {
        $nivel = Nivel::create($request->validated());

        $this->registrarMovimiento('CREAR', 'niveles', 'Nivel', $nivel->id_nivel, "Nivel creado: {$nivel->nombre}", null, $nivel->only(['codigo', 'nombre', 'estado']));

        return redirect()->route('niveles.index')->with('success', 'Nivel creado correctamente.');
    }

    public function edit(Nivel $nivel)
    {
        return view('niveles.edit', compact('nivel'));
    }

    public function update(NivelRequest $request, Nivel $nivel)
    {
        $anteriores = $nivel->only(['codigo', 'nombre', 'estado']);

        $nivel->update($request->validated());

        $this->registrarMovimiento('ACTUALIZAR', 'niveles', 'Nivel', $nivel->id_nivel, "Nivel actualizado: {$nivel->nombre}", $anteriores, $nivel->only(['codigo', 'nombre', 'estado']));

        return redirect()->route('niveles.index')->with('success', 'Nivel actualizado correctamente.');
    }

    public function destroy(Nivel $nivel)
    {
        $grados = $nivel->grados()->count();
        $matriculas = $nivel->matriculas()->count();

        if ($grados > 0 || $matriculas > 0) {
            return back()->with('error', "No se puede eliminar el nivel \"{$nivel->nombre}\": tiene {$grados} grado(s) y {$matriculas} matrícula(s) asociadas. Desactívelo en su lugar.");
        }

        $nombre = $nivel->nombre;
        $id = $nivel->id_nivel;

        DB::transaction(fn () => $nivel->delete());

        $this->registrarMovimiento('ELIMINAR', 'niveles', 'Nivel', $id, "Nivel eliminado: {$nombre}");

        return redirect()->route('niveles.index')->with('success', 'Nivel eliminado correctamente.');
    }
}
