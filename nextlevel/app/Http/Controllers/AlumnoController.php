<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Nivel;
use App\Models\Grado;
use Illuminate\Http\Request;

class AlumnoController extends Controller
{
    public function index(Request $request)
    {
        $query = Alumno::with(['grado.nivel', 'apoderado']);

        // Filtro por nivel
        if ($request->has('nivel') && $request->nivel != '') {
            $query->whereHas('grado', function($q) use ($request) {
                $q->where('id_nivel', $request->nivel);
            });
        }

        // Filtro por grado
        if ($request->has('grado') && $request->grado != '') {
            $query->where('id_grado', $request->grado);
        }

        // Filtro por búsqueda
        if ($request->has('busqueda') && $request->busqueda != '') {
            $busqueda = $request->busqueda;
            $query->where(function($q) use ($busqueda) {
                $q->where('nombres', 'LIKE', "%{$busqueda}%")
                  ->orWhere('apellidos', 'LIKE', "%{$busqueda}%")
                  ->orWhere('dni', 'LIKE', "%{$busqueda}%");
            });
        }

        $alumnos = $query->orderBy('apellidos')->paginate(20);

        $niveles = Nivel::where('estado', 'ACTIVO')->get();
        $grados = Grado::with('nivel')->where('estado', 'ACTIVO')->orderBy('orden')->get();

        return view('alumnos.index', compact('alumnos', 'niveles', 'grados'));
    }

    public function create()
    {
        $niveles = Nivel::where('estado', 'ACTIVO')->get();
        $grados = Grado::with('nivel')->where('estado', 'ACTIVO')->orderBy('orden')->get();

        return view('alumnos.create', compact('niveles', 'grados'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo' => 'nullable|string|max:30|unique:alumnos,codigo',
            'dni' => 'nullable|string|size:8|unique:alumnos,dni',
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'fecha_nacimiento' => 'nullable|date',
            'sexo' => 'nullable|in:F,M,OTRO,NO_DECLARA',
            'celular' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:120',
            'direccion' => 'nullable|string|max:255',
            'id_grado' => 'nullable|exists:grados,id_grado',
            'id_apoderado' => 'nullable|exists:apoderados,id_apoderado',
            'parentesco' => 'nullable|string|max:20',
            'estado' => 'required|in:ACTIVO,INACTIVO',
        ]);

        Alumno::create($validated);

        return redirect()->route('alumnos.index')
                        ->with('success', '¡Alumno creado exitosamente!');
    }

    public function show(Alumno $alumno)
    {
        $alumno->load(['grado.nivel', 'apoderado', 'matriculas']);
        return view('alumnos.show', compact('alumno'));
    }

    public function edit(Alumno $alumno)
    {
        $niveles = Nivel::where('estado', 'ACTIVO')->get();
        $grados = Grado::with('nivel')->where('estado', 'ACTIVO')->orderBy('orden')->get();

        return view('alumnos.edit', compact('alumno', 'niveles', 'grados'));
    }

    public function update(Request $request, Alumno $alumno)
    {
        $validated = $request->validate([
            'codigo' => 'nullable|string|max:30|unique:alumnos,codigo,' . $alumno->id_alumno . ',id_alumno',
            'dni' => 'nullable|string|size:8|unique:alumnos,dni,' . $alumno->id_alumno . ',id_alumno',
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'fecha_nacimiento' => 'nullable|date',
            'sexo' => 'nullable|in:F,M,OTRO,NO_DECLARA',
            'celular' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:120',
            'direccion' => 'nullable|string|max:255',
            'id_grado' => 'nullable|exists:grados,id_grado',
            'id_apoderado' => 'nullable|exists:apoderados,id_apoderado',
            'parentesco' => 'nullable|string|max:20',
            'estado' => 'required|in:ACTIVO,INACTIVO',
        ]);

        $alumno->update($validated);

        return redirect()->route('alumnos.index')
                        ->with('success', '¡Alumno actualizado exitosamente!');
    }

    public function destroy(Alumno $alumno)
    {
        $alumno->delete();

        return redirect()->route('alumnos.index')
                        ->with('success', '¡Alumno eliminado exitosamente!');
    }
}
