<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistroMovimiento extends Model
{
    protected $table = 'registro_movimientos';
    protected $primaryKey = 'id_registro';

    protected $fillable = [
        'user_id',
        'accion',
        'modulo',
        'entidad',
        'entidad_id',
        'descripcion',
        'filtros',
        'valores_anteriores',
        'valores_nuevos',
        'ip_address',
    ];

    protected $casts = [
        'filtros' => 'array',
        'valores_anteriores' => 'array',
        'valores_nuevos' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
