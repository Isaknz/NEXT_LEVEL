<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeriodoAcademico extends Model
{
    protected $table = 'periodos_academicos';
    protected $primaryKey = 'id_periodo';

    protected $fillable = [
        'codigo',
        'nombre',
        'anio',
        'fecha_inicio',
        'fecha_fin',
        'estado',
    ];

    public function matriculas()
    {
        return $this->hasMany(Matricula::class, 'id_periodo', 'id_periodo');
    }
}
