<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CuentaPorCobrar extends Model
{
    protected $table = 'cuentas_por_cobrar';
    protected $primaryKey = 'id_cuenta';

    protected $fillable = [
        'id_matricula',
        'id_concepto',
        'referencia',
        'descripcion',
        'fecha_emision',
        'fecha_vencimiento',
        'monto_original',
        'descuento',
        'recargo',
        'estado',
        'creado_por',
        'updated_by',
    ];

    public function matricula()
    {
        return $this->belongsTo(Matricula::class, 'id_matricula', 'id_matricula');
    }

    public function concepto()
    {
        return $this->belongsTo(ConceptoCobro::class, 'id_concepto', 'id_concepto');
    }
}
