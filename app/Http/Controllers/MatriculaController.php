<?php

namespace App\Http\Controllers;

use App\Models\Matricula;
use App\Models\Alumno;
use App\Models\Nivel;
use App\Models\Grado;
use App\Models\PeriodoAcademico;
use App\Models\CicloAcademia;
use App\Traits\RegistraMovimientos;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MatriculaController extends Controller
{
    use RegistraMovimientos;

    public function index(Request $request)
    {
        $query = Matricula::with(['alumno', 'periodo', 'nivel', 'grado']);

        // Filtro por periodo
        if ($request->has('periodo') && $request->periodo != '') {
            $query->where('id_periodo', $request->periodo);
        }

        // Filtro por nivel
        if ($request->has('nivel') && $request->nivel != '') {
            $query->where('id_nivel', $request->nivel);
        }

        // Filtro por modalidad
        if ($request->has('modalidad') && $request->modalidad != '') {
            $query->where('modalidad', $request->modalidad);
        }

        // Filtro por estado
        if ($request->has('estado') && $request->estado != '') {
            $query->where('estado', $request->estado);
        }

        // Búsqueda
        if ($request->has('busqueda') && $request->busqueda != '') {
            $busqueda = $request->busqueda;
            $query->where(function($q) use ($busqueda) {
                $q->where('codigo', 'LIKE', "%{$busqueda}%")
                  ->orWhereHas('alumno', function($sub) use ($busqueda) {
                      $sub->where('nombres', 'LIKE', "%{$busqueda}%")
                          ->orWhere('apellidos', 'LIKE', "%{$busqueda}%")
                          ->orWhere('dni', 'LIKE', "%{$busqueda}%");
                  });
            });
        }

        $matriculas = $query->orderBy('created_at', 'desc')->paginate(20);

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
        $alumnos = Alumno::where('estado', 'ACTIVO')->orderBy('apellidos')->get();
        $periodos = PeriodoAcademico::orderBy('anio', 'desc')->get();
        $niveles = Nivel::where('estado', 'ACTIVO')->get();
        $grados = Grado::with('nivel')->where('estado', 'ACTIVO')->orderBy('orden')->get();
        $ciclos = CicloAcademia::with(['periodo', 'facultad'])->orderBy('nombre')->get();

        return view('matriculas.create', compact('alumnos', 'periodos', 'niveles', 'grados', 'ciclos'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo' => [
                'required',
                'string',
                'max:40',
                Rule::unique('matriculas', 'codigo')
            ],
            'id_alumno' => 'required|exists:alumnos,id_alumno',
            'id_periodo' => 'required|exists:periodos_academicos,id_periodo',
            'id_nivel' => 'required|exists:niveles,id_nivel',
            'modalidad' => 'required|in:ESCOLAR,ACADEMIA',
            'id_grado' => 'required_if:modalidad,ESCOLAR|nullable|exists:grados,id_grado',
            'id_ciclo' => 'required_if:modalidad,ACADEMIA|nullable|exists:ciclos_academia,id_ciclo',
            'fecha_matricula' => 'required|date',
            'tipo_matricula' => 'required|in:NUEVO,REGULAR,TRASLADO,REINGRESO',
            'estado' => 'required|in:PENDIENTE,ACTIVA,RETIRADA,ANULADA,FINALIZADA',
            'observaciones' => 'nullable|string|max:500',
        ]);

        // Validar que no exista una matrícula activa para el mismo alumno y periodo
        $existe = Matricula::where('id_alumno', $validated['id_alumno'])
                          ->where('id_periodo', $validated['id_periodo'])
                          ->whereIn('estado', ['ACTIVA', 'PENDIENTE'])
                          ->exists();

        if ($existe) {
            return back()->withErrors([
                'id_alumno' => 'Este alumno ya tiene una matrícula activa en este periodo.'
            ])->withInput();
        }

        $validated['registrado_por'] = auth()->id();

        $matricula = Matricula::create($validated);

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
        $alumnos = Alumno::where('estado', 'ACTIVO')->orderBy('apellidos')->get();
        $periodos = PeriodoAcademico::orderBy('anio', 'desc')->get();
        $niveles = Nivel::where('estado', 'ACTIVO')->get();
        $grados = Grado::with('nivel')->where('estado', 'ACTIVO')->orderBy('orden')->get();
        $ciclos = CicloAcademia::with(['periodo', 'facultad'])->orderBy('nombre')->get();

        return view('matriculas.edit', compact('matricula', 'alumnos', 'periodos', 'niveles', 'grados', 'ciclos'));
    }

    public function update(Request $request, Matricula $matricula)
    {
        $valoresAnteriores = $matricula->toArray();

        $validated = $request->validate([
            'codigo' => [
                'required',
                'string',
                'max:40',
                Rule::unique('matriculas', 'codigo')->ignore($matricula->id_matricula, 'id_matricula')
            ],
            'id_alumno' => 'required|exists:alumnos,id_alumno',
            'id_periodo' => 'required|exists:periodos_academicos,id_periodo',
            'id_nivel' => 'required|exists:niveles,id_nivel',
            'modalidad' => 'required|in:ESCOLAR,ACADEMIA',
            'id_grado' => 'required_if:modalidad,ESCOLAR|nullable|exists:grados,id_grado',
            'id_ciclo' => 'required_if:modalidad,ACADEMIA|nullable|exists:ciclos_academia,id_ciclo',
            'fecha_matricula' => 'required|date',
            'tipo_matricula' => 'required|in:NUEVO,REGULAR,TRASLADO,REINGRESO',
            'estado' => 'required|in:PENDIENTE,ACTIVA,RETIRADA,ANULADA,FINALIZADA',
            'observaciones' => 'nullable|string|max:500',
        ]);

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
        // Verificar que no tenga pagos
        if ($matricula->pagos()->count() > 0) {
            return redirect()->route('matriculas.index')
                            ->with('error', 'No se puede eliminar una matrícula que tiene pagos registrados.');
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
