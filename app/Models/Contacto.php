<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contacto extends Model
{
    use HasFactory;

    protected $table = 'contactos';
    protected $primaryKey = 'idContacto';

    protected $fillable = [
        'id_Datos',
        'tipo',
        'telefono',
        'telefonoDos',
        'email'
    ];

    public function datos()
    {
        return $this->belongsTo(Datos::class, 'id_Datos');
    }
}