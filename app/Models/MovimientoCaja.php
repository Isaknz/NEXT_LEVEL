<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovimientoCaja extends Model
{
    protected $table = 'movimientos_caja';
    protected $primaryKey = 'id_movimiento';

    protected $fillable = [
        'id_caja',
        'tipo',
        'origen',
        'id_pago',
        'id_gasto',
        'fecha_movimiento',
        'monto',
        'descripcion',
        'estado',
        'registrado_por',
    ];

    protected $casts = [
        'fecha_movimiento' => 'datetime',
    ];

    public function caja()
    {
        return $this->belongsTo(Caja::class, 'id_caja', 'id_caja');
    }

    public function pago()
    {
        return $this->belongsTo(Pago::class, 'id_pago', 'id_pago');
    }

    public function gasto()
    {
        return $this->belongsTo(Gasto::class, 'id_gasto', 'id_gasto');
    }

    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por', 'id');
    }
}
