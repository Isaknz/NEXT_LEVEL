<?php

namespace App\Http\Controllers;

use App\Models\Grado;
use Illuminate\Http\Request;

class GradoController extends Controller
{
    public function index() { return view('grados.index'); }
    public function create() {
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        return view('grados.create');
    }
    public function store(Request $request) {
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        $validated = $request->validate([
            'id_nivel' => 'required|exists:niveles,id_nivel',
            'nombre' => 'required|string|max:30',
            'orden' => 'required|integer',
            'estado' => 'required|string|max:10',
        ]);
        Grado::create($validated);
        return redirect()->route('grados.index')->with('success', 'Grado creado');
    }
    public function show(Grado $grado) { return view('grados.show', compact('grado')); }
    public function edit(Grado $grado) {
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        return view('grados.edit', compact('grado'));
    }
    public function update(Request $request, Grado $grado) {
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        $validated = $request->validate([
            'id_nivel' => 'required|exists:niveles,id_nivel',
            'nombre' => 'required|string|max:30',
            'orden' => 'required|integer',
            'estado' => 'required|string|max:10',
        ]);
        $grado->update($validated);
        return redirect()->route('grados.index')->with('success', 'Grado actualizado');
    }
    public function destroy(Grado $grado) {
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        Grado::destroy($grado->id_grado);
        return redirect()->route('grados.index')->with('success', 'Grado eliminado');
    }
}
