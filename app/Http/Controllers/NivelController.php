<?php

namespace App\Http\Controllers;

use App\Models\Nivel;
use Illuminate\Http\Request;

class NivelController extends Controller
{
    public function index() { return view('niveles.index'); }
    public function create() {
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        return view('niveles.create');
    }
    public function store(Request $request) { return redirect()->back(); }
    public function show(Nivel $nivel) { return view('niveles.show', compact('nivel')); }
    public function edit(Nivel $nivel) {
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        return view('niveles.edit', compact('nivel'));
    }
    public function update(Request $request, Nivel $nivel) {
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        return redirect()->back();
    }
    public function destroy(Nivel $nivel) {
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        return redirect()->back();
    }
}
