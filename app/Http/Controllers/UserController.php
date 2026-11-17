<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->filtrar($request)
            ->orderBy('nombre')
            ->paginate(20)
            ->withQueryString();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        return view('users.create');
    }

    public function store(UserStoreRequest $request)
    {
        $validated = $request->validated();

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        // El sistema exige correo verificado: se envía el enlace apenas se
        // crea la cuenta. Si el SMTP falla no se bloquea la creación; el
        // usuario puede reenviarlo desde /email/verify.
        try {
            $user->sendEmailVerificationNotification();
        } catch (\Throwable $e) {
            \Log::warning('No se pudo enviar el correo de verificación', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->route('users.index')
                        ->with('success', '¡Usuario creado exitosamente!');
    }

    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        return view('users.edit', compact('user'));
    }

    public function update(UserUpdateRequest $request, User $user)
    {
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        $validated = $request->validated();

        // Degradar o desactivar al último administrador activo dejaría el
        // sistema sin nadie capaz de gestionar usuarios.
        $eraAdminActivo = $user->role === 'admin' && $user->estado === 'activo';
        $seguiraAdminActivo = $validated['role'] === 'admin' && $validated['estado'] === 'activo';

        if ($eraAdminActivo && ! $seguiraAdminActivo && $this->esUltimoAdminActivo($user)) {
            return redirect()->route('users.index')
                            ->with('error', 'No puedes degradar ni desactivar al último administrador activo');
        }

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('users.index')
                        ->with('success', '¡Usuario actualizado exitosamente!');
    }

    public function destroy(User $user)
    {
        if (auth()->user()->role === 'cajero') {
            return abort(403, 'Sin permisos');
        }
        // No permitir eliminarse a sí mismo
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                            ->with('error', 'No puedes eliminar tu propio usuario');
        }

        // No permitir eliminar al último administrador activo.
        if ($user->role === 'admin' && $user->estado === 'activo' && $this->esUltimoAdminActivo($user)) {
            return redirect()->route('users.index')
                            ->with('error', 'No puedes eliminar al último administrador activo');
        }

        $user->delete();

        return redirect()->route('users.index')
                        ->with('success', '¡Usuario eliminado exitosamente!');
    }

    /**
     * Comprueba si, excluyendo al usuario indicado, no queda ningún
     * administrador activo en el sistema.
     */
    private function esUltimoAdminActivo(User $user): bool
    {
        return User::where('role', 'admin')
            ->where('estado', 'activo')
            ->where('id', '!=', $user->id)
            ->doesntExist();
    }
}
