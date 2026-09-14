<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    protected $table = 'cajas';
    protected $primaryKey = 'id_caja';

    protected $fillable = [
        'codigo',
        'nombre',
        'tipo',
        'estado',
    ];

    public function movimientos()
    {
        return $this->hasMany(MovimientoCaja::class, 'id_caja', 'id_caja');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'id_caja', 'id_caja');
    }

    public function gastos()
    {
        return $this->hasMany(Gasto::class, 'id_caja', 'id_caja');
    }
}
