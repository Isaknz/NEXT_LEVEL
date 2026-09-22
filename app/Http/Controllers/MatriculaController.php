<?php

namespace App\Http\Controllers;

use App\Http\Requests\MatriculaRequest;
use App\Models\Matricula;
use App\Models\Alumno;
use App\Models\Nivel;
use App\Models\Grado;
use App\Models\PeriodoAcademico;
use App\Models\CicloAcademia;
use App\Models\Pago;
use App\Traits\RegistraMovimientos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

        $matricula = null;

        try {
            DB::transaction(function () use ($validated, &$matricula) {
                $matricula = Matricula::create($validated);

                if (!empty($request->input('pagos_iniciales'))) {
                    foreach ($request->input('pagos_iniciales') as $pagoData) {
                        $pago = \App\Models\Pago::create([
                            'codigo' => 'PAG-' . $matricula->codigo . '-' . now()->format('ymd'),
                            'id_matricula' => $matricula->id_matricula,
                            'id_caja' => $pagoData['id_caja'],
                            'fecha_pago' => now()->format('Y-m-d H:i:s'),
                            'metodo_pago' => $pagoData['metodo_pago'] ?? 'EFECTIVO',
                            'monto_total' => $pagoData['monto'],
                            'estado' => 'CONFIRMADO',
                            'registrado_por' => auth()->id(),
                        ]);

                        if ($pago->monto_total >= $matricula->cuentasPorCobrar()->whereIn('estado', ['PENDIENTE', 'PARCIAL'])->sum('monto_pendiente')) {
                            $matricula->update(['estado' => 'PAGADA']);
                        }
                    }
                }
            });
        } catch (\Throwable $e) {
            Log::error('Error al registrar matrícula', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withErrors([
                'error' => 'Ocurrió un error al registrar la matrícula.',
            ])->withInput();
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
