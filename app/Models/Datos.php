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
        'sexo',
        'dni',
        'fechaCaducidadDni',
        'nacionalidad',
        'residePeru',
        'nivelEducativo',
        'profesion',
        'enfermedadesPreexistentes',
        'ruc',
        'expuesta',
    ];

    protected $casts = [
        'expuesta' => 'boolean',
        'enfermedadesPreexistentes' => 'boolean',
        'residePeru' => 'boolean',
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
