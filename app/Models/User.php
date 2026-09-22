<?php

namespace App\Models;

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
