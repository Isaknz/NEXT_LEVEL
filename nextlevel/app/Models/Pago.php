<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $table = 'pagos';
    protected $primaryKey = 'id_pago';

    protected $fillable = [
        'codigo',
        'id_matricula',
        'id_caja',
        'fecha_pago',
        'metodo_pago',
        'numero_operacion',
        'monto_total',
        'estado',
        'observaciones',
        'registrado_por',
    ];

    public function matricula()
    {
        return $this->belongsTo(Matricula::class, 'id_matricula', 'id_matricula');
    }

    public function caja()
    {
        return $this->belongsTo(Caja::class, 'id_caja', 'id_caja');
    }

    public function detalles()
    {
        return $this->hasMany(PagoDetalle::class, 'id_pago', 'id_pago');
    }
}
