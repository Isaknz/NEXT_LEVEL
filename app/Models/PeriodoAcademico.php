<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeriodoAcademico extends Model
{
    use HasFactory;

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

    protected $casts = [
        'anio' => 'integer',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'estado' => 'string',
    ];

    public function matriculas()
    {
        return $this->hasMany(Matricula::class, 'id_periodo', 'id_periodo');
    }

    public function ciclos()
    {
        return $this->hasMany(CicloAcademia::class, 'id_periodo', 'id_periodo');
    }
}
