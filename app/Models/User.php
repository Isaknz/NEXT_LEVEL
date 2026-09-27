<?php

namespace App\Models;

use App\Support\Permisos;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nombre',
        'email',
        'password',
        'role',
        'estado',
        'last_login',
        'password_changed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_login' => 'datetime',
        'password_changed_at' => 'datetime',
    ];

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isGerente()
    {
        return $this->role === 'gerente';
    }

    public function isSecretaria()
    {
        return $this->role === 'secretaria';
    }

    public function isCajero()
    {
        return $this->role === 'cajero';
    }

    /**
     * Indica si el usuario puede acceder a la ruta indicada.
     * El menú lateral lo usa para no mostrar enlaces que darían 403.
     */
    public function puedeAcceder(?string $routeName): bool
    {
        return Permisos::permite($this->role, $routeName);
    }

    /**
     * Indica si el usuario puede ver el módulo indicado (ruta índice).
     */
    public function puedeVerModulo(string $modulo): bool
    {
        return $this->puedeAcceder($modulo . '.index');
    }

    public function scopeFiltrar(Builder $query, Request $request): Builder
    {
        return $query
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->role))
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->estado))
            ->when($request->filled('busqueda'), function ($q) use ($request) {
                $q->where(function ($inner) use ($request) {
                    $b = $request->busqueda;
                    $inner->where('nombre', 'LIKE', "%{$b}%")
                          ->orWhere('email', 'LIKE', "%{$b}%");
                });
            });
    }
}
