<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoriaGastoRequest;
use App\Models\CategoriaGasto;
use App\Traits\RegistraMovimientos;
use Illuminate\Support\Facades\DB;

class CategoriaGastoController extends Controller
{
    use RegistraMovimientos;

    public function index()
    {
        $categorias = CategoriaGasto::withCount('gastos')
            ->orderBy('nombre')
            ->paginate(15);

        return view('categorias.index', compact('categorias'));
    }

    public function create()
    {
        return view('categorias.create');
    }

    public function store(CategoriaGastoRequest $request)
    {
        $categoria = CategoriaGasto::create($request->validated());

        $this->registrarMovimiento('CREAR', 'categorias', 'Categoria', $categoria->id_categoria_gasto, "Categoría creada: {$categoria->nombre}", null, $categoria->only(['nombre', 'estado']));

        return redirect()->route('categorias.index')->with('success', 'Categoría creada correctamente.');
    }

    public function edit(CategoriaGasto $categoria)
    {
        return view('categorias.edit', compact('categoria'));
    }

    public function update(CategoriaGastoRequest $request, CategoriaGasto $categoria)
    {
        $anteriores = $categoria->only(['nombre', 'estado']);

        $categoria->update($request->validated());

        $this->registrarMovimiento('ACTUALIZAR', 'categorias', 'Categoria', $categoria->id_categoria_gasto, "Categoría actualizada: {$categoria->nombre}", $anteriores, $categoria->only(['nombre', 'estado']));

        return redirect()->route('categorias.index')->with('success', 'Categoría actualizada correctamente.');
    }

    public function destroy(CategoriaGasto $categoria)
    {
        $gastos = $categoria->gastos()->count();

        if ($gastos > 0) {
            return back()->with('error', "No se puede eliminar la categoría \"{$categoria->nombre}\": tiene {$gastos} gasto(s) asociado(s). Desactívela en su lugar.");
        }

        $nombre = $categoria->nombre;
        $id = $categoria->id_categoria_gasto;

        DB::transaction(fn () => $categoria->delete());

        $this->registrarMovimiento('ELIMINAR', 'categorias', 'Categoria', $id, "Categoría eliminada: {$nombre}");

        return redirect()->route('categorias.index')->with('success', 'Categoría eliminada correctamente.');
    }
}
