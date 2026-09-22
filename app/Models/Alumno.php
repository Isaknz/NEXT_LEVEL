<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;

class Alumno extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'alumnos';
    protected $primaryKey = 'id_alumno';

    protected $fillable = [
        'codigo',
        'dni',
        'nombres',
        'apellidos',
        'fecha_nacimiento',
        'sexo',
        'celular',
        'email',
        'direccion',
        'id_grado',
        'id_apoderado',
        'parentesco',
        'estado',
        'foto',
        'fecha_ingreso',
        'observaciones',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'fecha_ingreso' => 'date',
    ];

    public function grado()
    {
        return $this->belongsTo(Grado::class, 'id_grado', 'id_grado');
    }

    // Relación con apoderados a través de la tabla pivot
    public function apoderados()
    {
        return $this->belongsToMany(Apoderado::class, 'alumno_apoderado', 'id_alumno', 'id_apoderado')
                    ->withPivot('parentesco', 'es_principal', 'autorizado_recojo');
    }

    // Para compatibilidad con el campo directo id_apoderado
    public function apoderado()
    {
        return $this->belongsTo(Apoderado::class, 'id_apoderado', 'id_apoderado');
    }

    public function matriculas()
    {
        return $this->hasMany(Matricula::class, 'id_alumno', 'id_alumno');
    }

    public function getNombreCompletoAttribute()
    {
        return $this->apellidos . ', ' . $this->nombres;
    }

    public function getEsAcademiaAttribute()
    {
        return str_starts_with($this->codigo ?? '', 'AC');
    }

    // Obtener el apoderado principal
    public function getApoderadoPrincipalAttribute()
    {
        $principal = $this->apoderados()->wherePivot('es_principal', 1)->first();
        return $principal ?? $this->apoderados()->first();
    }

    public function scopeFiltrar(Builder $query, Request $request): Builder
    {
        return $query
            ->when($request->filled('nivel'), function ($q) use ($request) {
                if ($request->nivel == 3) {
                    $q->where('codigo', 'LIKE', 'AC%');
                } else {
                    $q->whereHas('grado', function ($g) use ($request) {
                        $g->where('id_nivel', $request->nivel);
                    });
                }
            })
            ->when($request->filled('grado'), fn ($q) => $q->where('id_grado', $request->grado))
            ->when($request->filled('busqueda'), function ($q) use ($request) {
                $q->where(function ($inner) use ($request) {
                    $b = $request->busqueda;
                    $inner->where('nombres', 'LIKE', "%{$b}%")
                          ->orWhere('apellidos', 'LIKE', "%{$b}%")
                          ->orWhere('dni', 'LIKE', "%{$b}%")
                          ->orWhere('codigo', 'LIKE', "%{$b}%");
                });
            });
    }
}
