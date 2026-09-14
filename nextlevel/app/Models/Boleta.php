<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Boleta extends Model
{
    protected $table = 'boletas';
    protected $primaryKey = 'id_boleta';

    protected $fillable = [
        'id_ingreso',
        'id_gasto',
        'numero_boleta',
        'fecha',
        'monto',
        'tipo',
        'archivo_pdf',
        'observaciones',
    ];

    public function ingreso()
    {
        return $this->belongsTo(Ingreso::class, 'id_ingreso', 'id_ingreso');
    }

    public function gasto()
    {
        return $this->belongsTo(Gasto::class, 'id_gasto', 'id_gasto');
    }
}
