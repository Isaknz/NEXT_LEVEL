<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComprobantePago extends Model
{
    protected $table = 'comprobantes_pago';
    protected $primaryKey = 'id_comprobante_pago';

    protected $fillable = [
        'id_pago',
        'tipo',
        'serie',
        'numero',
        'fecha_emision',
        'archivo_url',
    ];

    protected $casts = [
        'fecha_emision' => 'datetime',
    ];

    public function pago()
    {
        return $this->belongsTo(Pago::class, 'id_pago', 'id_pago');
    }
}
