<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Datos extends Model
{
    use HasFactory;

    protected $table = 'datos';

    protected $fillable = [
        'nombre',
        'apellidoPaterno',
        'apellidoMaterno',
        'apellidoConyuge',
        'estadoCivil',
        'dni',
        'fechaCaducidadDni',
        'ruc',
        'expuesta',
        'aval'
    ];

    protected $casts = [
        'expuesta' => 'boolean',
        'aval' => 'boolean'
    ];

    public function usuario()
    {
        return $this->hasOne(User::class, 'idDatos', 'idDatos');
    }

    public function contactos()
    {
        return $this->hasMany(Contacto::class, 'idDatos');
    }
    
}
