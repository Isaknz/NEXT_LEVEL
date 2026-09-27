<?php

namespace App\Http\Controllers;

use App\Http\Requests\FacultadRequest;
use App\Models\Facultad;
use App\Traits\RegistraMovimientos;
use Illuminate\Support\Facades\DB;

class FacultadController extends Controller
{
    use RegistraMovimientos;

    public function index()
    {
        $facultades = Facultad::withCount('ciclos')
            ->orderBy('nombre')
            ->paginate(15);

        return view('facultades.index', compact('facultades'));
    }

    public function create()
    {
        return view('facultades.create');
    }

    public function store(FacultadRequest $request)
    {
        $facultad = Facultad::create($request->validated());

        $this->registrarMovimiento('CREAR', 'facultades', 'Facultad', $facultad->id_facultad, "Facultad creada: {$facultad->nombre}", null, $facultad->only(['nombre', 'estado']));

        return redirect()->route('facultades.index')->with('success', 'Facultad creada correctamente.');
    }

    public function edit(Facultad $facultad)
    {
        return view('facultades.edit', compact('facultad'));
    }

    public function update(FacultadRequest $request, Facultad $facultad)
    {
        $anteriores = $facultad->only(['nombre', 'estado']);

        $facultad->update($request->validated());

        $this->registrarMovimiento('ACTUALIZAR', 'facultades', 'Facultad', $facultad->id_facultad, "Facultad actualizada: {$facultad->nombre}", $anteriores, $facultad->only(['nombre', 'estado']));

        return redirect()->route('facultades.index')->with('success', 'Facultad actualizada correctamente.');
    }

    public function destroy(Facultad $facultad)
    {
        $ciclos = $facultad->ciclos()->count();

        if ($ciclos > 0) {
            return back()->with('error', "No se puede eliminar la facultad \"{$facultad->nombre}\": tiene {$ciclos} ciclo(s) asociados. Desactívela en su lugar.");
        }

        $nombre = $facultad->nombre;
        $id = $facultad->id_facultad;

        DB::transaction(fn () => $facultad->delete());

        $this->registrarMovimiento('ELIMINAR', 'facultades', 'Facultad', $id, "Facultad eliminada: {$nombre}");

        return redirect()->route('facultades.index')->with('success', 'Facultad eliminada correctamente.');
    }
}
