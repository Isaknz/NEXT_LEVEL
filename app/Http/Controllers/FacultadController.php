<?php

namespace App\Http\Controllers;

use App\Models\Facultad;
use Illuminate\Http\Request;

class FacultadController extends Controller
{
    public function index() { return view('facultades.index'); }
    public function create() {
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        return view('facultades.create');
    }
    public function store(Request $request) { return redirect()->back(); }
    public function show(Facultad $facultad) { return view('facultades.show', compact('facultad')); }
    public function edit(Facultad $facultad) {
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        return view('facultades.edit', compact('facultad'));
    }
    public function update(Request $request, Facultad $facultad) {
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        return redirect()->back();
    }
    public function destroy(Facultad $facultad) {
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        return redirect()->back();
    }
}
