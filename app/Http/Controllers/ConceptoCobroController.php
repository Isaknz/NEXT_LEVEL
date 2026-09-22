<?php

namespace App\Http\Controllers;

use App\Models\ConceptoCobro;
use Illuminate\Http\Request;

class ConceptoCobroController extends Controller
{
    public function index() { return view('conceptos.index'); }
    public function create() {
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        return view('conceptos.create');
    }
    public function store(Request $request) { return redirect()->back(); }
    public function show(ConceptoCobro $concepto) { return view('conceptos.show', compact('concepto')); }
    public function edit(ConceptoCobro $concepto) {
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        return view('conceptos.edit', compact('concepto'));
    }
    public function update(Request $request, ConceptoCobro $concepto) {
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        return redirect()->back();
    }
    public function destroy(ConceptoCobro $concepto) {
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        return redirect()->back();
    }
}
