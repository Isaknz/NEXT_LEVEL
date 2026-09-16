<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConceptoCobro extends Model
{
    protected $table = 'conceptos_cobro';
    protected $primaryKey = 'id_concepto';

    protected $fillable = [
        'codigo',
        'nombre',
        'tipo',
        'modalidad_aplicable',
        'monto_referencial',
        'estado',
    ];

    public function cuentasPorCobrar()
    {
        return $this->hasMany(CuentaPorCobrar::class, 'id_concepto', 'id_concepto');
    }
}
