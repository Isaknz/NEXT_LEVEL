<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mensualidad extends Model
{
    protected $table = 'mensualidades';
    protected $primaryKey = 'id_pago';

    protected $fillable = [
        'id_alumno',
        'mes',
        'anio',
        'monto',
        'fecha_pago',
        'estado',
    ];

    public function alumno()
    {
        return $this->belongsTo(Alumno::class, 'id_alumno', 'id_alumno');
    }
}
