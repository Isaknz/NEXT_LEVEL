<?php

namespace App\Http\Controllers;

use App\Http\Requests\GradoRequest;
use App\Models\Grado;
use App\Models\Nivel;
use App\Traits\RegistraMovimientos;
use Illuminate\Support\Facades\DB;

class GradoController extends Controller
{
    use RegistraMovimientos;

    public function index()
    {
        $grados = Grado::with('nivel')
            ->withCount(['alumnos', 'matriculas'])
            ->orderBy('id_nivel')
            ->orderBy('orden')
            ->paginate(15);

        return view('grados.index', compact('grados'));
    }

    public function create()
    {
        $niveles = Nivel::where('estado', 'ACTIVO')->orderBy('nombre')->get();

        return view('grados.create', compact('niveles'));
    }

    public function store(GradoRequest $request)
    {
        $grado = Grado::create($request->validated());

        $this->registrarMovimiento('CREAR', 'grados', 'Grado', $grado->id_grado, "Grado creado: {$grado->nombre}", null, $grado->only(['id_nivel', 'nombre', 'orden', 'estado']));

        return redirect()->route('grados.index')->with('success', 'Grado creado correctamente.');
    }

    public function edit(Grado $grado)
    {
        $niveles = Nivel::where('estado', 'ACTIVO')
            ->orWhere('id_nivel', $grado->id_nivel)
            ->orderBy('nombre')
            ->get();

        return view('grados.edit', compact('grado', 'niveles'));
    }

    public function update(GradoRequest $request, Grado $grado)
    {
        $anteriores = $grado->only(['id_nivel', 'nombre', 'orden', 'estado']);

        $grado->update($request->validated());

        $this->registrarMovimiento('ACTUALIZAR', 'grados', 'Grado', $grado->id_grado, "Grado actualizado: {$grado->nombre}", $anteriores, $grado->only(['id_nivel', 'nombre', 'orden', 'estado']));

        return redirect()->route('grados.index')->with('success', 'Grado actualizado correctamente.');
    }

    public function destroy(Grado $grado)
    {
        $alumnos = $grado->alumnos()->count();
        $matriculas = $grado->matriculas()->count();

        if ($alumnos > 0 || $matriculas > 0) {
            return back()->with('error', "No se puede eliminar el grado \"{$grado->nombre}\": tiene {$alumnos} alumno(s) y {$matriculas} matrícula(s) asociadas. Desactívelo en su lugar.");
        }

        $nombre = $grado->nombre;
        $id = $grado->id_grado;

        DB::transaction(fn () => $grado->delete());

        $this->registrarMovimiento('ELIMINAR', 'grados', 'Grado', $id, "Grado eliminado: {$nombre}");

        return redirect()->route('grados.index')->with('success', 'Grado eliminado correctamente.');
    }
}
