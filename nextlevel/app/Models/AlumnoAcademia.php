<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlumnoAcademia extends Model
{
    protected $table = 'alumnos_academia';
    protected $primaryKey = 'id_alumno';

    protected $fillable = [
        'nombre',
        'apellido',
        'dni',
        'facultad',
        'celular',
        'turno',
        'id_apoderado',
        'parentesco',
    ];

    public function apoderado()
    {
        return $this->belongsTo(Apoderado::class, 'id_apoderado', 'id_apoderado');
    }

    public function getNombreCompletoAttribute()
    {
        return $this->nombre . ' ' . $this->apellido;
    }
}
