<?php

namespace App\Http\Controllers;

use App\Models\CicloAcademia;
use Illuminate\Http\Request;

class CicloAcademiaController extends Controller
{
    public function index() { return view('ciclos.index'); }
    public function create() {
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        return view('ciclos.create');
    }
    public function store(Request $request) { return redirect()->back(); }
    public function show(CicloAcademia $ciclo) { return view('ciclos.show', compact('ciclo')); }
    public function edit(CicloAcademia $ciclo) {
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        return view('ciclos.edit', compact('ciclo'));
    }
    public function update(Request $request, CicloAcademia $ciclo) {
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        return redirect()->back();
    }
    public function destroy(CicloAcademia $ciclo) {
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        return redirect()->back();
    }
}
