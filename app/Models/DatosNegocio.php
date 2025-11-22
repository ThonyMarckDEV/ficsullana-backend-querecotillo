<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DatosNegocio extends Model
{
    use HasFactory;

    protected $table = 'datos_negocio';

    protected $fillable = [
        'id_Evaluacion', 'otros_ingresos_sector', 'otros_ingresos_tiempo', 'riesgo_sector',
        'otros_ingresos_monto', 'otros_ingresos_frecuencia', 'depende_otros_ingresos',
        'sustento_otros_ingresos', 'tiene_medios_pago', 'descripcion_medios_pago',
        'zona_ubicacion', 'modalidad_atencion', 'restriccion_actual', 'ventas_diarias',
        'cuenta_con_ahorros', 'ahorros_sustentables', 'fecha_ultima_compra', 'monto_ultima_compra',
        'variacion_compras_mes_anterior', 'cuentas_por_cobrar_monto', 'cuentas_por_cobrar_num_clientes',
        'tiempo_recuperacion', 'foto_apuntes_cobranza', 'detalle_activo_fijo', 'valor_actual_activo_fijo',
        'foto_activo_fijo', 'dias_efectivo', 'monto_efectivo', 'pagos_realizados_mes',
        'gastos_administrativos_fijos', 'gastos_operativos_variables', 'imprevistos_mermas',
        'promedio_ventas_pdt', 'contribucion_essalud_anual', 'referencias_comerciales'
    ];

    public function detalleInventario()
    {
        return $this->hasMany(DetalleInventarioNegocio::class, 'id_Datos_Negocio');
    }
}