<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CicloAcademia extends Model
{
    use HasFactory;
    protected $table = 'ciclos_academia';
    protected $primaryKey = 'id_ciclo';

    protected $fillable = [
        'id_periodo',
        'id_facultad',
        'nombre',
        'turno',
        'fecha_inicio',
        'fecha_fin',
        'monto_referencial',
        'vacantes',
        'estado',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'monto_referencial' => 'decimal:2',
    ];

    public function periodo()
    {
        return $this->belongsTo(PeriodoAcademico::class, 'id_periodo', 'id_periodo');
    }

    public function facultad()
    {
        return $this->belongsTo(Facultad::class, 'id_facultad', 'id_facultad');
    }

    public function matriculas()
    {
        return $this->hasMany(Matricula::class, 'id_ciclo', 'id_ciclo');
    }
}
