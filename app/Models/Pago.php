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
        'anulado_por',
        'anulado_at',
        'motivo_anulacion',
    ];

    protected $casts = [
        'fecha_pago' => 'datetime',
        'anulado_at' => 'datetime',
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

    public function comprobante()
    {
        return $this->hasOne(ComprobantePago::class, 'id_pago', 'id_pago');
    }

    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por', 'id');
    }

    public function anuladoPor()
    {
        return $this->belongsTo(User::class, 'anulado_por', 'id');
    }

    public function movimientoCaja()
    {
        return $this->hasOne(MovimientoCaja::class, 'id_pago', 'id_pago');
    }
}
