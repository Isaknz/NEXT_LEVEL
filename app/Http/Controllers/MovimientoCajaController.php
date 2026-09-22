<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use App\Models\MovimientoCaja;
use App\Models\Gasto;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\RegistraMovimientos;

class MovimientoCajaController extends Controller
{
    use RegistraMovimientos;

    public function index(Request $request)
    {
        $movimientos = MovimientoCaja::query()
            ->with(['caja', 'pago', 'gasto', 'registradoPor'])
            ->filtrar($request)
            ->orderBy('fecha_movimiento', 'desc')
            ->paginate(20)
            ->withQueryString();

        $cajas = Caja::where('estado', 'ACTIVA')->get();

        return view('movimientos.index', compact('movimientos', 'cajas'));
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'id_caja' => 'required|exists:cajas,id_caja',
            'tipo' => 'required|in:INGRESO,EGRESO',
            'origen' => 'required|string|max:50',
            'id_pago' => 'nullable|exists:pagos,id_pago',
            'id_gasto' => 'nullable|exists:gastos,id_gasto',
            'monto' => 'required|numeric|min:0.01',
            'descripcion' => 'nullable|string|max:500',
            'fecha_movimiento' => 'required|date',
        ]);

        $movimiento = null;

        try {
            DB::transaction(function () use ($datos, &$movimiento) {
                $movimiento = MovimientoCaja::create([
                    'id_caja' => $datos['id_caja'],
                    'tipo' => $datos['tipo'],
                    'origen' => $datos['origen'],
                    'id_pago' => $datos['id_pago'] ?? null,
                    'id_gasto' => $datos['id_gasto'] ?? null,
                    'fecha_movimiento' => $datos['fecha_movimiento'],
                    'monto' => $datos['monto'],
                    'descripcion' => $datos['descripcion'],
                    'estado' => 'ACTIVO',
                    'registrado_por' => auth()->id(),
                ]);

                if ($datos['tipo'] === 'EGRESO' && $movimiento->id_gasto) {
                    $gasto = Gasto::find($movimiento->id_gasto);
                    if ($gasto) {
                        $gasto->update(['estado' => 'REGISTRADO']);
                    }
                }
            });
        } catch (\Throwable $e) {
            Log::error('Error al registrar movimiento de caja', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'datos' => $datos,
            ]);

            return back()->withErrors([
                'error' => 'Ocurrió un error al registrar el movimiento.',
            ])->withInput();
        }

        self::registrarMovimiento(
            'CREAR',
            'Movimientos de Caja',
            'MovimientoCaja',
            $movimiento->id_movimiento,
            "Registró movimiento de caja por S/. {$movimiento->monto}",
            null,
            $movimiento->toArray()
        );

        return redirect()->route('movimientos.index')
                        ->with('success', '¡Movimiento registrado exitosamente!');
    }

    public function destroy(MovimientoCaja $movimiento)
    {
        return back()->withErrors(['error' => 'Para eliminar un movimiento, primero debe anularlo.']);
    }
}
