<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Alumno extends Model
{
    use SoftDeletes;

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
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
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
}
