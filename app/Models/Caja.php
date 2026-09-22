<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    use HasFactory;

    protected $table = 'cajas';
    protected $primaryKey = 'id_caja';

    protected $fillable = [
        'codigo',
        'nombre',
        'tipo',
        'estado',
        'saldo_inicial',
        'monto_apertura',
        'monto_cierre',
        'fecha_cierre',
        'usuarios_id_cerrado',
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
