<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

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

    public function anuladoPor()
    {
        return $this->belongsTo(User::class, 'anulado_por', 'id');
    }

    public function movimientoCaja()
    {
        return $this->hasOne(MovimientoCaja::class, 'id_gasto', 'id_gasto');
    }

    public function scopeFiltrar(Builder $query, Request $request): Builder
    {
        return $query
            ->when($request->filled('caja'), fn ($q) => $q->where('id_caja', $request->caja))
            ->when($request->filled('categoria'), fn ($q) => $q->where('id_categoria_gasto', $request->categoria))
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->estado))
            ->when($request->filled('fecha_desde'), fn ($q) => $q->whereDate('fecha_gasto', '>=', $request->fecha_desde))
            ->when($request->filled('fecha_hasta'), fn ($q) => $q->whereDate('fecha_gasto', '<=', $request->fecha_hasta))
            ->when($request->filled('busqueda'), function ($q) use ($request) {
                $q->where(function ($inner) use ($request) {
                    $b = $request->busqueda;
                    $inner->where('codigo', 'LIKE', "%{$b}%")
                          ->orWhere('concepto', 'LIKE', "%{$b}%")
                          ->orWhere('proveedor', 'LIKE', "%{$b}%")
                          ->orWhere('numero_comprobante', 'LIKE', "%{$b}%");
                });
            });
    }
}
