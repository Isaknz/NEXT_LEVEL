<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistroMovimiento extends Model
{
    protected $table = 'registro_movimientos';
    protected $primaryKey = 'id_registro';

    // IMPORTANTE: La tabla solo tiene created_at, no updated_at
    public $timestamps = false;

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
        'created_at',
    ];

    protected $casts = [
        'filtros' => 'array',
        'valores_anteriores' => 'array',
        'valores_nuevos' => 'array',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function getUsuarioNombreAttribute()
    {
        return $this->user ? $this->user->nombre : 'Sistema';
    }

    public function getAccionColorAttribute()
    {
        return match($this->accion) {
            'CREAR' => 'green',
            'ACTUALIZAR' => 'blue',
            'ELIMINAR' => 'red',
            'ANULAR' => 'orange',
            'VER' => 'gray',
            'EXPORTAR' => 'purple',
            'IMPRIMIR' => 'indigo',
            'INICIAR_SESION' => 'teal',
            default => 'gray',
        };
    }

    public function getAccionIconoAttribute()
    {
        return match($this->accion) {
            'CREAR' => 'fa-plus-circle',
            'ACTUALIZAR' => 'fa-edit',
            'ELIMINAR' => 'fa-trash',
            'ANULAR' => 'fa-ban',
            'VER' => 'fa-eye',
            'EXPORTAR' => 'fa-file-export',
            'IMPRIMIR' => 'fa-print',
            'INICIAR_SESION' => 'fa-sign-in-alt',
            default => 'fa-circle',
        };
    }
}
