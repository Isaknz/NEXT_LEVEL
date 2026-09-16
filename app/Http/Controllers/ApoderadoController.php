<?php

namespace App\Http\Controllers;

use App\Models\Apoderado;
use Illuminate\Http\Request;

class ApoderadoController extends Controller
{
    public function index(Request $request)
    {
        $query = Apoderado::withCount('alumnos');

        if ($request->has('busqueda') && $request->busqueda != '') {
            $busqueda = $request->busqueda;
            $query->where(function($q) use ($busqueda) {
                $q->where('nombres', 'LIKE', "%{$busqueda}%")
                  ->orWhere('apellidos', 'LIKE', "%{$busqueda}%")
                  ->orWhere('dni', 'LIKE', "%{$busqueda}%")
                  ->orWhere('celular', 'LIKE', "%{$busqueda}%");
            });
        }

        if ($request->has('estado') && $request->estado != '') {
            $query->where('estado', $request->estado);
        }

        $apoderados = $query->orderBy('apellidos')->paginate(20);

        return view('apoderados.index', compact('apoderados'));
    }

    public function create()
    {
        return view('apoderados.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'dni' => 'nullable|string|size:8|unique:apoderados,dni',
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'celular' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:120',
            'direccion' => 'nullable|string|max:255',
            'estado' => 'required|in:ACTIVO,INACTIVO',
        ]);

        Apoderado::create($validated);

        return redirect()->route('apoderados.index')
                        ->with('success', '¡Apoderado creado exitosamente!');
    }

    public function show(Apoderado $apoderado)
    {
        $apoderado->load(['alumnos.grado.nivel']);
        return view('apoderados.show', compact('apoderado'));
    }

    public function edit(Apoderado $apoderado)
    {
        return view('apoderados.edit', compact('apoderado'));
    }

    public function update(Request $request, Apoderado $apoderado)
    {
        $validated = $request->validate([
            'dni' => 'nullable|string|size:8|unique:apoderados,dni,' . $apoderado->id_apoderado . ',id_apoderado',
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'celular' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:120',
            'direccion' => 'nullable|string|max:255',
            'estado' => 'required|in:ACTIVO,INACTIVO',
        ]);

        $apoderado->update($validated);

        return redirect()->route('apoderados.index')
                        ->with('success', '¡Apoderado actualizado exitosamente!');
    }

    public function destroy(Apoderado $apoderado)
    {
        $apoderado->delete();

        return redirect()->route('apoderados.index')
                        ->with('success', '¡Apoderado eliminado exitosamente!');
    }
}
