<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grado extends Model
{
    use HasFactory;

    protected $table = 'grados';
    protected $primaryKey = 'id_grado';

    protected $fillable = [
        'id_nivel',
        'nombre',
        'orden',
        'estado',
    ];

    protected $casts = [
        'orden' => 'integer',
        'estado' => 'string',
    ];

    public function nivel()
    {
        return $this->belongsTo(Nivel::class, 'id_nivel', 'id_nivel');
    }

    public function alumnos()
    {
        return $this->hasMany(Alumno::class, 'id_grado', 'id_grado');
    }

    public function matriculas()
    {
        return $this->hasMany(Matricula::class, 'id_grado', 'id_grado');
    }
}
