<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Apoderado extends Model
{
    use SoftDeletes;

    protected $table = 'apoderados';
    protected $primaryKey = 'id_apoderado';

    protected $fillable = [
        'dni',
        'nombres',
        'apellidos',
        'celular',
        'email',
        'direccion',
        'estado',
    ];

    public function alumnos()
    {
        return $this->hasMany(Alumno::class, 'id_apoderado', 'id_apoderado');
    }

    public function getNombreCompletoAttribute()
    {
        return $this->apellidos . ', ' . $this->nombres;
    }
}
