<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ingreso extends Model
{
    protected $table = 'ingresos';
    protected $primaryKey = 'id_ingreso';

    protected $fillable = [
        'id_alumno',
        'concepto',
        'tipo',
        'monto',
        'fecha',
        'descripcion',
    ];

    public function alumno()
    {
        return $this->belongsTo(Alumno::class, 'id_alumno', 'id_alumno');
    }
}
