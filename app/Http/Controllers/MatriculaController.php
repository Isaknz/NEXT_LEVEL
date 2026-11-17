<?php

namespace App\Http\Controllers;

use App\Http\Requests\MatriculaRequest;
use App\Models\Matricula;
use App\Models\Alumno;
use App\Models\Nivel;
use App\Models\Grado;
use App\Models\PeriodoAcademico;
use App\Models\CicloAcademia;
use App\Traits\RegistraMovimientos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class MatriculaController extends Controller
{
    use RegistraMovimientos;

    public function index(Request $request)
    {
        $matriculas = Matricula::query()
            ->with(['alumno', 'periodo', 'nivel', 'grado'])
            ->filtrar($request)
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $periodos = PeriodoAcademico::orderBy('anio', 'desc')->get();
        $niveles = Nivel::where('estado', 'ACTIVO')->get();

        // Estadísticas
        $stats = [
            'total' => Matricula::count(),
            'activas' => Matricula::where('estado', 'ACTIVA')->count(),
            'pendientes' => Matricula::where('estado', 'PENDIENTE')->count(),
            'retiradas' => Matricula::where('estado', 'RETIRADA')->count(),
        ];

        return view('matriculas.index', compact('matriculas', 'periodos', 'niveles', 'stats'));
    }

    public function create(Request $request)
    {
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        $alumnos = Alumno::where('estado', 'ACTIVO')->orderBy('apellidos')->get();
        $periodos = PeriodoAcademico::orderBy('anio', 'desc')->get();
        $niveles = Nivel::where('estado', 'ACTIVO')->get();
        $grados = Grado::with('nivel')->where('estado', 'ACTIVO')->orderBy('orden')->get();
        $ciclos = CicloAcademia::with(['periodo', 'facultad'])->orderBy('nombre')->get();

        return view('matriculas.create', compact('alumnos', 'periodos', 'niveles', 'grados', 'ciclos'));
    }

    public function store(MatriculaRequest $request)
    {
        $validated = $request->validated();
        $esAlumnoNuevo = empty($validated['id_alumno']) && $request->filled('alumno_nombres');

        // Solo un alumno ya registrado puede chocar con una matrícula existente.
        if (! $esAlumnoNuevo) {
            $existe = Matricula::where('id_alumno', $validated['id_alumno'])
                              ->where('id_periodo', $validated['id_periodo'])
                              ->where('modalidad', $validated['modalidad'])
                              ->whereIn('estado', ['ACTIVA', 'PENDIENTE'])
                              ->exists();

            if ($existe) {
                return back()->withErrors([
                    'id_alumno' => 'Este alumno ya tiene una matrícula activa en este periodo.'
                ])->withInput();
            }
        }

        // Los datos del alumno no son columnas de matriculas.
        $datosAlumno = [
            'nombres' => $request->input('alumno_nombres'),
            'apellidos' => $request->input('alumno_apellidos'),
            'dni' => $request->input('alumno_dni') ?: null,
            'fecha_nacimiento' => $request->input('alumno_fecha_nacimiento') ?: null,
            'sexo' => $request->input('alumno_sexo') ?: null,
            'celular' => $request->input('alumno_celular') ?: null,
            'email' => $request->input('alumno_email') ?: null,
            'id_grado' => $validated['id_grado'] ?? null,
            'modalidad' => $validated['modalidad'],
            'estado' => 'ACTIVO',
        ];

        $validated = array_diff_key($validated, array_flip([
            'alumno_nombres',
            'alumno_apellidos',
            'alumno_dni',
            'alumno_fecha_nacimiento',
            'alumno_sexo',
            'alumno_celular',
            'alumno_email',
        ]));

        $validated['registrado_por'] = auth()->id();
        $matricula = null;
        $alumno = null;

        try {
            DB::transaction(function () use (&$validated, &$matricula, &$alumno, $datosAlumno, $esAlumnoNuevo) {
                if ($esAlumnoNuevo) {
                    $alumno = Alumno::create($datosAlumno);

                    // Misma regla que en AlumnoController: los de academia
                    // sin código escrito reciben AC-##### para identificarlos.
                    if ($alumno->modalidad === 'ACADEMIA' && empty($alumno->codigo)) {
                        $alumno->update(['codigo' => 'AC-'.str_pad((string) $alumno->id_alumno, 5, '0', STR_PAD_LEFT)]);
                    }

                    $validated['id_alumno'] = $alumno->id_alumno;
                } else {
                    // El lock serializa el check de duplicado: dos altas
                    // simultáneas del mismo alumno+periodo+modalidad no
                    // pueden colarse ambas.
                    $existe = Matricula::where('id_alumno', $validated['id_alumno'])
                        ->where('id_periodo', $validated['id_periodo'])
                        ->where('modalidad', $validated['modalidad'])
                        ->whereIn('estado', ['ACTIVA', 'PENDIENTE'])
                        ->lockForUpdate()
                        ->exists();

                    if ($existe) {
                        throw ValidationException::withMessages([
                            'id_alumno' => 'Este alumno ya tiene una matrícula activa en este periodo.',
                        ]);
                    }
                }

                $matricula = Matricula::create($validated);
            });
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Error al registrar matrícula', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withErrors([
                'error' => 'Ocurrió un error al registrar la matrícula.',
            ])->withInput();
        }

        if ($alumno) {
            self::registrarMovimiento(
                'CREAR',
                'Alumnos',
                'Alumno',
                $alumno->id_alumno,
                "Creó al alumno {$alumno->nombres} {$alumno->apellidos} desde el registro de matrícula",
                null,
                $alumno->toArray()
            );
        }

        self::registrarMovimiento(
            'CREAR',
            'Matrículas',
            'Matricula',
            $matricula->id_matricula,
            "Creó la matrícula {$matricula->codigo} para el alumno {$matricula->alumno->nombres} {$matricula->alumno->apellidos}",
            null,
            $matricula->toArray()
        );

        return redirect()->route('matriculas.index')
                        ->with('success', '¡Matrícula creada exitosamente!');
    }

    public function show(Matricula $matricula)
    {
        $matricula->load(['alumno.apoderados', 'periodo', 'nivel', 'grado', 'ciclo.facultad', 'cuentasPorCobrar.concepto', 'pagos']);
        return view('matriculas.show', compact('matricula'));
    }

    public function edit(Matricula $matricula)
    {
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        $alumnos = Alumno::where('estado', 'ACTIVO')->orderBy('apellidos')->get();
        $periodos = PeriodoAcademico::orderBy('anio', 'desc')->get();
        $niveles = Nivel::where('estado', 'ACTIVO')->get();
        $grados = Grado::with('nivel')->where('estado', 'ACTIVO')->orderBy('orden')->get();
        $ciclos = CicloAcademia::with(['periodo', 'facultad'])->orderBy('nombre')->get();

        return view('matriculas.edit', compact('matricula', 'alumnos', 'periodos', 'niveles', 'grados', 'ciclos'));
    }

    public function update(MatriculaRequest $request, Matricula $matricula)
    {
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        $valoresAnteriores = $matricula->toArray();

        $validated = $request->validated();

        // Una matrícula PAGADA tiene todas sus cuentas saldadas: devolverla a
        // ACTIVA/PENDIENTE a mano la reabre sin generar la deuda correspondiente.
        // Solo puede pasar a estados terminales o seguir PAGADA.
        if ($matricula->estado === 'PAGADA'
            && in_array($validated['estado'], ['ACTIVA', 'PENDIENTE'], true)) {
            return redirect()->route('matriculas.index')
                ->with('error', 'No se puede reabrir una matrícula pagada. Si el alumno debe dinero, cree primero las cuentas por cobrar correspondientes.');
        }

        $validated['updated_by'] = auth()->id();

        $matricula->update($validated);

        self::registrarMovimiento(
            'ACTUALIZAR',
            'Matrículas',
            'Matricula',
            $matricula->id_matricula,
            "Actualizó la matrícula {$matricula->codigo}",
            $valoresAnteriores,
            $matricula->fresh()->toArray()
        );

        return redirect()->route('matriculas.index')
                        ->with('success', '¡Matrícula actualizada exitosamente!');
    }

    public function destroy(Matricula $matricula)
    {
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        // Los pagos y las cuentas por cobrar apuntan a la matrícula con una
        // clave foránea restrictiva: si existen, el DELETE terminaría en un
        // error 500 en lugar de un mensaje entendible.
        if ($matricula->pagos()->exists()) {
            return redirect()->route('matriculas.index')
                            ->with('error', 'No se puede eliminar una matrícula que tiene pagos registrados.');
        }

        if ($matricula->cuentasPorCobrar()->exists()) {
            return redirect()->route('matriculas.index')
                            ->with('error', 'No se puede eliminar una matrícula que tiene cuentas por cobrar. Anule primero esas cuentas.');
        }

        $codigo = $matricula->codigo;
        $id = $matricula->id_matricula;
        $valores = $matricula->toArray();

        $matricula->delete();

        self::registrarMovimiento(
            'ELIMINAR',
            'Matrículas',
            'Matricula',
            $id,
            "Eliminó la matrícula {$codigo}",
            $valores,
            null
        );

        return redirect()->route('matriculas.index')
                        ->with('success', '¡Matrícula eliminada exitosamente!');
    }
}
