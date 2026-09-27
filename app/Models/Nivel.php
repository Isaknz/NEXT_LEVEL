<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nivel extends Model
{
    use HasFactory;

    protected $table = 'niveles';
    protected $primaryKey = 'id_nivel';

    protected $fillable = [
        'codigo',
        'nombre',
        'estado',
    ];

    protected $casts = [
        'estado' => 'string',
    ];

    public function grados()
    {
        return $this->hasMany(Grado::class, 'id_nivel', 'id_nivel');
    }

    public function matriculas()
    {
        return $this->hasMany(Matricula::class, 'id_nivel', 'id_nivel');
    }
}
