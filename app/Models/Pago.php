<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

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

    public function scopeFiltrar(Builder $query, Request $request): Builder
    {
        return $query
            ->when($request->filled('caja'), fn ($q) => $q->where('id_caja', $request->caja))
            ->when($request->filled('metodo'), fn ($q) => $q->where('metodo_pago', $request->metodo))
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->estado))
            ->when($request->filled('fecha_desde'), fn ($q) => $q->whereDate('fecha_pago', '>=', $request->fecha_desde))
            ->when($request->filled('fecha_hasta'), fn ($q) => $q->whereDate('fecha_pago', '<=', $request->fecha_hasta))
            ->when($request->filled('busqueda'), function ($q) use ($request) {
                $q->where(function ($inner) use ($request) {
                    $q2 = $request->busqueda;
                    $inner->where('codigo', 'LIKE', "%{$q2}%")
                          ->orWhere('numero_operacion', 'LIKE', "%{$q2}%")
                          ->orWhereHas('matricula.alumno', function ($sub) use ($q2) {
                              $sub->where('nombres', 'LIKE', "%{$q2}%")
                                  ->orWhere('apellidos', 'LIKE', "%{$q2}%")
                                  ->orWhere('dni', 'LIKE', "%{$q2}%");
                          });
                });
            });
    }
}
