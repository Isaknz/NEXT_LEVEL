<?php

namespace App\Http\Controllers;

use App\Models\PeriodoAcademico;
use Illuminate\Http\Request;

class PeriodoAcademicoController extends Controller
{
    public function index() { return view('periodos.index'); }
    public function create() {
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        return view('periodos.create');
    }
    public function store(Request $request) { return redirect()->back(); }
    public function show(PeriodoAcademico $periodo) { return view('periodos.show', compact('periodo')); }
    public function edit(PeriodoAcademico $periodo) {
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        return view('periodos.edit', compact('periodo'));
    }
    public function update(Request $request, PeriodoAcademico $periodo) {
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        return redirect()->back();
    }
    public function destroy(PeriodoAcademico $periodo) {
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        return redirect()->back();
    }
}
