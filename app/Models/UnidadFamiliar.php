<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UnidadFamiliar extends Model
{
    use HasFactory;

    protected $table = 'unidad_familiar';

    protected $fillable = [
        'id_Evaluacion', 'numero_miembros', 'gastos_alimentacion', 'gastos_educacion',
        'detalle_educacion', 'gastos_servicios', 'gastos_movilidad', 'tiene_deudas_ifis',
        'ifi_1_nombre', 'ifi_1_cuota', 'ifi_2_nombre', 'ifi_2_cuota', 'ifi_3_nombre', 'ifi_3_cuota',
        'gastos_salud', 'frecuencia_salud', 'detalle_salud', 'total_gastos_mensuales'
    ];
}