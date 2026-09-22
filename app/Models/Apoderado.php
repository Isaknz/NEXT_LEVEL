<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;

class Apoderado extends Model
{
    use SoftDeletes;

    protected $table = 'apoderados';
    protected $primaryKey = 'id_apoderado';

    protected $fillable = [
        'dni',
        'nombres',
        'apellidos',
        'celular',
        'email',
        'direccion',
        'estado',
    ];

    // Relación con alumnos a través de la tabla pivot
    public function alumnos()
    {
        return $this->belongsToMany(Alumno::class, 'alumno_apoderado', 'id_apoderado', 'id_alumno')
                    ->withPivot('parentesco', 'es_principal', 'autorizado_recojo');
    }

    public function getNombreCompletoAttribute()
    {
        return $this->apellidos . ', ' . $this->nombres;
    }

    public function scopeFiltrar(Builder $query, Request $request): Builder
    {
        return $query
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->estado))
            ->when($request->filled('busqueda'), function ($q) use ($request) {
                $q->where(function ($inner) use ($request) {
                    $b = $request->busqueda;
                    $inner->where('nombres', 'LIKE', "%{$b}%")
                          ->orWhere('apellidos', 'LIKE', "%{$b}%")
                          ->orWhere('dni', 'LIKE', "%{$b}%")
                          ->orWhere('celular', 'LIKE', "%{$b}%");
                });
            });
    }
}
