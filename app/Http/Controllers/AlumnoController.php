<?php

namespace App\Http\Controllers;

use App\Http\Requests\AlumnoRequest;
use App\Models\Alumno;
use App\Models\Apoderado;
use App\Models\Grado;
use App\Models\Nivel;
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
        $apoderados = Apoderado::orderBy('apellidos')->get();

        return view('alumnos.create', compact('niveles', 'grados', 'apoderados'));
    }

    public function store(AlumnoRequest $request)
    {
        $validated = $request->validated();
        $this->limpiarCamposPorModalidad($validated);

        $alumno = Alumno::create($validated);

        $this->generarCodigoSiFalta($alumno);

        $this->vincularApoderado($alumno, $validated);

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

    /**
     * La modalidad define que campo aplica: ESCOLAR usa grado, ACADEMIA usa ciclo.
     */
    private function limpiarCamposPorModalidad(array &$validated): void
    {
        if (($validated['modalidad'] ?? 'ESCOLAR') === 'ACADEMIA') {
            $validated['id_grado'] = null;
        }
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
        $apoderados = Apoderado::orderBy('apellidos')->get();

        return view('alumnos.edit', compact('alumno', 'niveles', 'grados', 'apoderados'));
    }

    public function update(AlumnoRequest $request, Alumno $alumno)
    {
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        $valoresAnteriores = $alumno->toArray();

        $validated = $request->validated();
        $this->limpiarCamposPorModalidad($validated);

        $alumno->update($validated);

        $this->generarCodigoSiFalta($alumno);

        $this->vincularApoderado($alumno, $validated);

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

        // El borrado es lógico, pero dejaría matrículas, cuentas y pagos
        // apuntando a un alumno invisible para el sistema. Se exige cerrar
        // primero su historial académico.
        if ($alumno->matriculas()->exists()) {
            return redirect()->route('alumnos.index')
                            ->with('error', "No se puede eliminar al alumno \"{$nombre}\" porque tiene matrículas registradas. Retíralas o cámbiale el estado a INACTIVO.");
        }

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

    /**
     * Los alumnos de academia llevan código AC-##### para identificarlos en
     * listados e impresiones. Si el usuario no escribió uno, se genera con
     * el id ya asignado (único por construcción).
     */
    private function generarCodigoSiFalta(Alumno $alumno): void
    {
        if ($alumno->modalidad === 'ACADEMIA' && empty($alumno->codigo)) {
            $alumno->update([
                'codigo' => 'AC-'.str_pad((string) $alumno->id_alumno, 5, '0', STR_PAD_LEFT),
            ]);
        }
    }

    /**
     * Sincroniza el vínculo del alumno con su apoderado (campo directo y pivot).
     */
    private function vincularApoderado(Alumno $alumno, array $datos): void
    {
        // Si el formulario no envía el campo (p. ej. ediciones parciales),
        // no tocar los vínculos existentes.
        if (! array_key_exists('id_apoderado', $datos)) {
            return;
        }

        $idApoderado = $datos['id_apoderado'];

        if (! $idApoderado) {
            $alumno->apoderados()->detach();

            return;
        }

        $parentesco = $datos['parentesco'] ?? 'TUTOR';

        // El formulario maneja al apoderado principal. No borrar otros
        // vínculos secundarios creados desde la ficha del apoderado:
        // solo degradarlos a no-principales.
        $alumno->apoderados()->newPivotStatement()
            ->where('id_alumno', $alumno->id_alumno)
            ->update(['es_principal' => false]);

        $alumno->apoderados()->syncWithoutDetaching([
            $idApoderado => [
                'parentesco' => $parentesco,
                'es_principal' => true,
                'autorizado_recojo' => true,
            ],
        ]);

        // Refrescar el parentesco si el vínculo ya existía.
        $alumno->apoderados()->updateExistingPivot($idApoderado, [
            'parentesco' => $parentesco,
            'es_principal' => true,
        ]);
    }
}
