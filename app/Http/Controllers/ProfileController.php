<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);
        $user = auth()->user();
        $user->update([
            'nombre' => $validated['nombre'],
            'email' => $validated['email'],
            'password' => isset($validated['password']) ? Hash::make($validated['password']) : $user->password,
        ]);
        return redirect()->back()->with('status', 'Perfil actualizado');
    }
}
