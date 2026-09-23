<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facultad extends Model
{
    use HasFactory;
    protected $table = 'facultades';
    protected $primaryKey = 'id_facultad';

    protected $fillable = [
        'nombre',
        'estado',
    ];

    public function ciclos()
    {
        return $this->hasMany(CicloAcademia::class, 'id_facultad', 'id_facultad');
    }
}
