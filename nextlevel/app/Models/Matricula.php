<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Matricula extends Model
{
    protected $table = 'matriculas';
    protected $primaryKey = 'id_matricula';

    protected $fillable = [
        'codigo',
        'id_alumno',
        'id_periodo',
        'id_nivel',
        'modalidad',
        'id_grado',
        'id_ciclo',
        'fecha_matricula',
        'tipo_matricula',
        'estado',
        'observaciones',
        'registrado_por',
        'updated_by',
    ];

    public function alumno()
    {
        return $this->belongsTo(Alumno::class, 'id_alumno', 'id_alumno');
    }

    public function periodo()
    {
        return $this->belongsTo(PeriodoAcademico::class, 'id_periodo', 'id_periodo');
    }

    public function nivel()
    {
        return $this->belongsTo(Nivel::class, 'id_nivel', 'id_nivel');
    }

    public function grado()
    {
        return $this->belongsTo(Grado::class, 'id_grado', 'id_grado');
    }
}
