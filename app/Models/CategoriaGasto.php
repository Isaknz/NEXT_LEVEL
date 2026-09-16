<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoriaGasto extends Model
{
    protected $table = 'categorias_gasto';
    protected $primaryKey = 'id_categoria_gasto';

    protected $fillable = [
        'nombre',
        'estado',
    ];

    public function gastos()
    {
        return $this->hasMany(Gasto::class, 'id_categoria_gasto', 'id_categoria_gasto');
    }
}
