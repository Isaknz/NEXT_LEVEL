<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit');
    }

    public function update(Request $request)
    {
        $user = $request->user();

        // El perfil es personal: cualquier rol autenticado (incluido el
        // cajero) puede actualizar sus propios datos. El correo debe seguir
        // siendo único para no provocar un error de base de datos.
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        $emailCambio = $validated['email'] !== $user->email;

        $user->nombre = $validated['nombre'];
        $user->email = $validated['email'];

        // Al cambiar de correo, la verificación anterior deja de ser válida.
        if ($emailCambio) {
            $user->email_verified_at = null;
        }

        if (!empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();

        return redirect()->back()->with('status', 'Perfil actualizado');
    }
}
