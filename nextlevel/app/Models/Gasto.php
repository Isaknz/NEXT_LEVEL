<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gasto extends Model
{
    protected $table = 'gastos';
    protected $primaryKey = 'id_gasto';

    protected $fillable = [
        'codigo',
        'id_categoria_gasto',
        'id_caja',
        'fecha_gasto',
        'proveedor',
        'concepto',
        'descripcion',
        'monto',
        'tipo_comprobante',
        'serie_comprobante',
        'numero_comprobante',
        'archivo_url',
        'estado',
        'registrado_por',
        'anulado_por',
        'anulado_at',
        'motivo_anulacion',
    ];

    protected $casts = [
        'fecha_gasto' => 'datetime',
        'anulado_at' => 'datetime',
    ];

    public function categoria()
    {
        return $this->belongsTo(CategoriaGasto::class, 'id_categoria_gasto', 'id_categoria_gasto');
    }

    public function caja()
    {
        return $this->belongsTo(Caja::class, 'id_caja', 'id_caja');
    }

    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por', 'id');
    }
}
