<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApoderadoRequest;
use App\Models\Apoderado;
use App\Models\Alumno;
use App\Traits\RegistraMovimientos;
use Illuminate\Http\Request;

class ApoderadoController extends Controller
{
    use RegistraMovimientos;

    public function index(Request $request)
    {
        $apoderados = Apoderado::query()
            ->withCount('alumnos')
            ->filtrar($request)
            ->orderBy('apellidos')
            ->paginate(20)
            ->withQueryString();

        return view('apoderados.index', compact('apoderados'));
    }

    public function create()
    {
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        $alumnos = Alumno::where('estado','ACTIVO')->orderBy('apellidos')->orderBy('nombres')->get();
        return view('apoderados.create', compact('alumnos'));
    }

    public function store(ApoderadoRequest $request)
    {
        $apoderado = Apoderado::create($request->validated());
        $this->sincronizarAlumnos($apoderado, $request->input('alumnos', []));

        self::registrarMovimiento(
            'CREAR',
            'Apoderados',
            'Apoderado',
            $apoderado->id_apoderado,
            "Creó al apoderado {$apoderado->nombres} {$apoderado->apellidos}",
            null,
            $apoderado->toArray()
        );

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
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        $alumnos = Alumno::where('estado','ACTIVO')->orderBy('apellidos')->orderBy('nombres')->get();
        $alumnosVinculados = $apoderado->alumnos()->pluck('alumnos.id_alumno')->all();
        return view('apoderados.edit', compact('apoderado', 'alumnos', 'alumnosVinculados'));
    }

    public function update(ApoderadoRequest $request, Apoderado $apoderado)
    {
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        $valoresAnteriores = $apoderado->toArray();

        $apoderado->update($request->validated());
        $this->sincronizarAlumnos($apoderado, $request->input('alumnos', []));

        self::registrarMovimiento(
            'ACTUALIZAR',
            'Apoderados',
            'Apoderado',
            $apoderado->id_apoderado,
            "Actualizó al apoderado {$apoderado->nombres} {$apoderado->apellidos}",
            $valoresAnteriores,
            $apoderado->fresh()->toArray()
        );

        return redirect()->route('apoderados.index')
                        ->with('success', '¡Apoderado actualizado exitosamente!');
    }

    public function destroy(Apoderado $apoderado)
    {
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        $nombre = "{$apoderado->nombres} {$apoderado->apellidos}";
        $id = $apoderado->id_apoderado;
        $valores = $apoderado->toArray();

        $apoderado->delete();

        self::registrarMovimiento(
            'ELIMINAR',
            'Apoderados',
            'Apoderado',
            $id,
            "Eliminó al apoderado {$nombre}",
            $valores,
            null
        );

        return redirect()->route('apoderados.index')
                        ->with('success', '¡Apoderado eliminado exitosamente!');
    }

    /**
     * Vincula/desvincula alumnos desde el formulario del apoderado.
     * El parentesco y los permisos se asignan desde la ficha del alumno.
     */
    private function sincronizarAlumnos(Apoderado $apoderado, array $ids): void
    {
        $ids = array_map('intval', $ids);
        $actuales = $apoderado->alumnos()->pluck('alumnos.id_alumno')->map(fn ($i) => (int) $i)->all();

        // Solo desvincular los que ya no están; los que permanecen conservan
        // su parentesco y permisos (se editan desde la ficha del alumno).
        $apoderado->alumnos()->detach(array_diff($actuales, $ids));

        foreach (array_diff($ids, $actuales) as $idAlumno) {
            $apoderado->alumnos()->attach($idAlumno, [
                'parentesco' => 'TUTOR',
                'es_principal' => false,
                'autorizado_recojo' => true,
            ]);
        }
    }
}
