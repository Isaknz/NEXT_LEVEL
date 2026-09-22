<?php

namespace App\Http\Controllers;

use App\Models\CategoriaGasto;
use Illuminate\Http\Request;

class CategoriaGastoController extends Controller
{
    public function index() { return view('categorias.index'); }
    public function create() {
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        return view('categorias.create');
    }
    public function store(Request $request) { return redirect()->back(); }
    public function show(CategoriaGasto $categoria) { return view('categorias.show', compact('categoria')); }
    public function edit(CategoriaGasto $categoria) {
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        return view('categorias.edit', compact('categoria'));
    }
    public function update(Request $request, CategoriaGasto $categoria) {
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        return redirect()->back();
    }
    public function destroy(CategoriaGasto $categoria) {
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        return redirect()->back();
    }
}
