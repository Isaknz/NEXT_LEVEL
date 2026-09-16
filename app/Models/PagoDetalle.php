<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagoDetalle extends Model
{
    protected $table = 'pago_detalles';
    protected $primaryKey = 'id_pago_detalle';

    public $timestamps = false;

    protected $fillable = [
        'id_pago',
        'id_cuenta',
        'monto_aplicado',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function pago()
    {
        return $this->belongsTo(Pago::class, 'id_pago', 'id_pago');
    }

    public function cuentaPorCobrar()
    {
        return $this->belongsTo(CuentaPorCobrar::class, 'id_cuenta', 'id_cuenta');
    }
}
