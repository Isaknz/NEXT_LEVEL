<?php

namespace App\Http\Controllers;

use App\Http\Requests\AlumnoRequest;
use App\Models\Alumno;
use App\Models\Nivel;
use App\Models\Grado;
use App\Traits\RegistraMovimientos;
use Illuminate\Http\Request;

class AlumnoController extends Controller
{
    use RegistraMovimientos;

    public function index(Request $request)
    {
        $alumnos = Alumno::query()
            ->with(['grado.nivel', 'apoderados'])
            ->filtrar($request)
            ->orderBy('apellidos')
            ->paginate(20)
            ->withQueryString();

        $niveles = Nivel::where('estado', 'ACTIVO')->get();
        $grados = Grado::with('nivel')->where('estado', 'ACTIVO')->orderBy('orden')->get();

        return view('alumnos.index', compact('alumnos', 'niveles', 'grados'));
    }

    public function create()
    {
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        $niveles = Nivel::where('estado', 'ACTIVO')->get();
        $grados = Grado::with('nivel')->where('estado', 'ACTIVO')->orderBy('orden')->get();

        return view('alumnos.create', compact('niveles', 'grados'));
    }

    public function store(AlumnoRequest $request)
    {
        $validated = $request->validated();

        $alumno = Alumno::create($validated);

        // Registrar movimiento
        self::registrarMovimiento(
            'CREAR',
            'Alumnos',
            'Alumno',
            $alumno->id_alumno,
            "Creó al alumno: {$alumno->nombres} {$alumno->apellidos}",
            null,
            $alumno->toArray()
        );

        return redirect()->route('alumnos.index')
                        ->with('success', '¡Alumno creado exitosamente!');
    }

    public function show(Alumno $alumno)
    {
        $alumno->load(['grado.nivel', 'apoderados', 'matriculas.periodo', 'matriculas.nivel']);
        return view('alumnos.show', compact('alumno'));
    }

    public function edit(Alumno $alumno)
    {
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        $niveles = Nivel::where('estado', 'ACTIVO')->get();
        $grados = Grado::with('nivel')->where('estado', 'ACTIVO')->orderBy('orden')->get();

        return view('alumnos.edit', compact('alumno', 'niveles', 'grados'));
    }

    public function update(AlumnoRequest $request, Alumno $alumno)
    {
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        $valoresAnteriores = $alumno->toArray();

        $validated = $request->validated();

        $alumno->update($validated);

        // Registrar movimiento
        self::registrarMovimiento(
            'ACTUALIZAR',
            'Alumnos',
            'Alumno',
            $alumno->id_alumno,
            "Actualizó al alumno: {$alumno->nombres} {$alumno->apellidos}",
            $valoresAnteriores,
            $alumno->fresh()->toArray()
        );

        return redirect()->route('alumnos.index')
                        ->with('success', '¡Alumno actualizado exitosamente!');
    }

    public function destroy(Alumno $alumno)
    {
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        $nombre = "{$alumno->nombres} {$alumno->apellidos}";
        $id = $alumno->id_alumno;
        $valores = $alumno->toArray();

        // Soft delete (marca con deleted_at, no borra permanentemente)
        $alumno->delete();

        // Registrar movimiento
        self::registrarMovimiento(
            'ELIMINAR',
            'Alumnos',
            'Alumno',
            $id,
            "Eliminó al alumno: {$nombre}",
            $valores,
            null
        );

        return redirect()->route('alumnos.index')
                        ->with('success', '¡Alumno eliminado exitosamente!');
    }
}
