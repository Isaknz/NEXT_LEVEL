<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
}
