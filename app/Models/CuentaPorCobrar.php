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
        'anulado_at',
        'motivo_anulacion',
        'creado_por',
        'updated_by',
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'fecha_vencimiento' => 'date',
        'anulado_at' => 'datetime',
    ];

    public function matricula()
    {
        return $this->belongsTo(Matricula::class, 'id_matricula', 'id_matricula');
    }

    public function concepto()
    {
        return $this->belongsTo(ConceptoCobro::class, 'id_concepto', 'id_concepto');
    }

    public function pagoDetalles()
    {
        return $this->hasMany(PagoDetalle::class, 'id_cuenta', 'id_cuenta');
    }

    public function getMontoPendienteAttribute()
    {
        $totalPagado = $this->pagoDetalles()
                            ->whereHas('pago', function($q) {
                                $q->where('estado', 'CONFIRMADO');
                            })
                            ->sum('monto_aplicado');

        return $this->monto_original - $totalPagado - $this->descuento + $this->recargo;
    }

    public function getMontoTotalAttribute()
    {
        return $this->monto_original - $this->descuento + $this->recargo;
    }
}
