<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetalleInventarioNegocio extends Model
{
    use HasFactory;

    protected $table = 'detalle_inventario_negocio';

    protected $fillable = [
        'id_Datos_Negocio', 'nombre_producto', 'unidad_medida',
        'precio_compra_unitario', 'precio_venta_unitario',
        'margen_ganancia', 'cantidad_inventario', 'precio_total_estimado'
    ];
}