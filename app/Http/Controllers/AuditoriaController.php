<?php

namespace App\Http\Controllers;

use App\Models\RegistroMovimiento;
use App\Models\User;
use Illuminate\Http\Request;

class AuditoriaController extends Controller
{
    public function index(Request $request)
    {
        $query = RegistroMovimiento::with('user');

        // Filtro por usuario
        if ($request->has('user_id') && $request->user_id != '') {
            $query->where('user_id', $request->user_id);
        }

        // Filtro por acción
        if ($request->has('accion') && $request->accion != '') {
            $query->where('accion', $request->accion);
        }

        // Filtro por módulo
        if ($request->has('modulo') && $request->modulo != '') {
            $query->where('modulo', $request->modulo);
        }

        // Filtro por fecha desde
        if ($request->has('fecha_desde') && $request->fecha_desde != '') {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }

        // Filtro por fecha hasta
        if ($request->has('fecha_hasta') && $request->fecha_hasta != '') {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        // Búsqueda en descripción
        if ($request->has('busqueda') && $request->busqueda != '') {
            $query->where('descripcion', 'LIKE', '%' . $request->busqueda . '%');
        }

        $movimientos = $query->orderBy('created_at', 'desc')->paginate(30);

        // Para los filtros
        $usuarios = User::orderBy('nombre')->get();
        $modulos = RegistroMovimiento::distinct()->pluck('modulo')->filter();
        $acciones = ['CREAR', 'ACTUALIZAR', 'ELIMINAR', 'ANULAR', 'VER', 'EXPORTAR', 'IMPRIMIR', 'INICIAR_SESION', 'CERRAR_SESION'];

        // Estadísticas
        $stats = [
            'total' => RegistroMovimiento::count(),
            'hoy' => RegistroMovimiento::whereDate('created_at', today())->count(),
            'semana' => RegistroMovimiento::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'mes' => RegistroMovimiento::whereMonth('created_at', now()->month)->count(),
        ];

        return view('auditoria.index', compact('movimientos', 'usuarios', 'modulos', 'acciones', 'stats'));
    }

    public function show(RegistroMovimiento $movimiento)
    {
        $movimiento->load('user');
        return view('auditoria.show', compact('movimiento'));
    }

    public function limpiar(Request $request)
    {
        $request->validate([
            'fecha_limite' => 'required|date|before:today',
        ]);

        $eliminados = RegistroMovimiento::whereDate('created_at', '<', $request->fecha_limite)->delete();

        return redirect()->route('auditoria.index')
                        ->with('success', "Se eliminaron {$eliminados} registros anteriores a {$request->fecha_limite}");
    }
}
