<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Garantia extends Model
{
    use HasFactory;

    protected $table = 'garantias';

    protected $fillable = [
        'id_Evaluacion', 'es_declaracion_jurada', 'moneda',
        'clase_garantia', 'documento_garantia', 'tipo_garantia',
        'descripcion_bien', 'direccion_bien', 'monto_garantia',
        'valor_comercial', 'valor_realizacion', 'ficha_registral',
        'fecha_ultima_valuacion'
    ];
}