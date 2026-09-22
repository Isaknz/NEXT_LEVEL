<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Matricula extends Model
{
    use HasFactory;

    protected $table = 'matriculas';
    protected $primaryKey = 'id_matricula';

    protected $fillable = [
        'codigo',
        'id_alumno',
        'id_periodo',
        'id_nivel',
        'modalidad',
        'id_grado',
        'id_ciclo',
        'fecha_matricula',
        'tipo_matricula',
        'estado',
        'observaciones',
        'registrado_por',
        'updated_by',
    ];

    protected $casts = [
        'fecha_matricula' => 'date',
    ];

    public function alumno()
    {
        return $this->belongsTo(Alumno::class, 'id_alumno', 'id_alumno');
    }

    public function periodo()
    {
        return $this->belongsTo(PeriodoAcademico::class, 'id_periodo', 'id_periodo');
    }

    public function nivel()
    {
        return $this->belongsTo(Nivel::class, 'id_nivel', 'id_nivel');
    }

    public function grado()
    {
        return $this->belongsTo(Grado::class, 'id_grado', 'id_grado');
    }

    public function ciclo()
    {
        return $this->belongsTo(CicloAcademia::class, 'id_ciclo', 'id_ciclo');
    }

    public function cuentasPorCobrar()
    {
        return $this->hasMany(CuentaPorCobrar::class, 'id_matricula', 'id_matricula');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'id_matricula', 'id_matricula');
    }

    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por', 'id');
    }

    // Accessor para total pagado
    public function getTotalPagadoAttribute()
    {
        return $this->pagos()->where('estado', 'CONFIRMADO')->sum('monto_total');
    }

    // Accessor para total de deuda
    public function getTotalDeudaAttribute()
    {
        return $this->cuentasPorCobrar()
                    ->whereIn('estado', ['PENDIENTE', 'PARCIAL'])
                    ->get()
                    ->sum(function($cuenta) {
                        return $cuenta->monto_pendiente;
                    });
    }

    public function scopeFiltrar(Builder $query, Request $request): Builder
    {
        return $query
            ->when($request->filled('periodo'), fn ($q) => $q->where('id_periodo', $request->periodo))
            ->when($request->filled('nivel'), fn ($q) => $q->where('id_nivel', $request->nivel))
            ->when($request->filled('modalidad'), fn ($q) => $q->where('modalidad', $request->modalidad))
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->estado))
            ->when($request->filled('busqueda'), function ($q) use ($request) {
                $q->where(function ($inner) use ($request) {
                    $b = $request->busqueda;
                    $inner->where('codigo', 'LIKE', "%{$b}%")
                          ->orWhereHas('alumno', function ($sub) use ($b) {
                              $sub->where('nombres', 'LIKE', "%{$b}%")
                                  ->orWhere('apellidos', 'LIKE', "%{$b}%")
                                  ->orWhere('dni', 'LIKE', "%{$b}%");
                          });
                });
            });
    }
}
