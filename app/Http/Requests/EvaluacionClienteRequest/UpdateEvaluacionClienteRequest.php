<?php

namespace App\Http\Requests\EvaluacionClienteRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEvaluacionClienteRequest extends FormRequest
{
    public function rules(): array
    {
        $usuarioId = $this->input('usuario.id', null);

        return [
            // ==========================================
            // 1. USUARIO (CLIENTE)
            // ==========================================
            'usuario.id' => 'required|integer|exists:datos,id',
            'usuario.dni' => [
                'required',
                'digits_between:8,9',
                Rule::unique('datos', 'dni')->ignore($usuarioId)
            ],
            'usuario.apellidoPaterno'      => 'required|string|max:100',
            'usuario.apellidoMaterno'      => 'nullable|string|max:100',
            'usuario.nombre'               => 'required|string|max:100',
            'usuario.fechaNacimiento'      => 'required|date',
            'usuario.fechaCaducidadDni'    => 'required|date',
            'usuario.sexo'                 => 'required|string|max:50',
            'usuario.estadoCivil'          => 'required|string|max:50',
            'usuario.nacionalidad'         => 'required|string|max:50',
            'usuario.residePeru'           => 'required|boolean',
            'usuario.nivelEducativo'       => 'nullable|string|max:100',
            'usuario.profesion'            => 'nullable|string|max:100',
            'usuario.telefonoFijo'         => 'nullable|digits_between:6,9',
            'usuario.telefonoMovil'        => 'required|digits:9',
            'usuario.correo'               => 'required|email|max:150',
            'usuario.enfermedadesPreexistentes' => 'required|boolean',
            'usuario.ctaAhorros'           => 'required|string|max:50',
            'usuario.entidadFinanciera'    => 'required|string|max:100',
            'usuario.direccionFiscal'      => 'required|string|max:255',
            'usuario.direccionCorrespondencia' => 'nullable|string|max:255',
            'usuario.tipoVivienda'         => 'nullable|string|max:50',
            'usuario.tiempoResidencia'     => 'nullable|string|max:50',
            'usuario.referenciaDomicilio'  => 'nullable|string|max:255',
            'usuario.centroLaboral'        => 'nullable|string|max:150',
            'usuario.ingresoMensual'       => 'required|numeric|min:0',
            'usuario.inicioLaboral'        => 'nullable|date',
            'usuario.situacionLaboral'     => 'required|string|max:50',
            'usuario.provincia'            => 'required|string|max:100',
            'usuario.departamento'         => 'required|string|max:100',
            'usuario.distrito'             => 'required|string|max:100',
            'usuario.expuestaPoliticamente' => 'required|boolean',

            // ==========================================
            // 2. CRÉDITO (TABLA PADRE)
            // ==========================================
            'credito.producto'             => 'required|string|max:100',
            'credito.montoPrestamo'        => 'required|numeric|min:100',
            'credito.tasaInteres'          => 'required|numeric|min:0|max:100',
            'credito.cuotas'               => 'required|integer|min:1',
            'credito.modalidadCredito'     => 'required|string|max:50',
            'credito.destinoCredito'       => 'required|string|max:150',
            'credito.periodoCredito'       => 'required|string|max:50',
            'credito.observaciones'        => 'nullable|string|max:500',

            // ==========================================
            // 3. DATOS NEGOCIO
            // ==========================================
            'datosNegocio'                 => 'nullable|array',
            'datosNegocio.ventas_diarias'  => 'nullable|numeric|min:0',
            'datosNegocio.monto_efectivo'  => 'nullable|numeric|min:0',
            'datosNegocio.zona_ubicacion'  => 'nullable|string|max:100',
            'datosNegocio.otros_ingresos_sector' => 'nullable|string|max:150',
            'datosNegocio.otros_ingresos_tiempo' => 'nullable|string|max:50',
            'datosNegocio.riesgo_sector'   => 'nullable|string|max:100',
            'datosNegocio.otros_ingresos_monto' => 'nullable|numeric|min:0',
            'datosNegocio.otros_ingresos_frecuencia' => 'nullable|string|max:50',
            'datosNegocio.depende_otros_ingresos' => 'nullable|boolean',
            'datosNegocio.sustento_otros_ingresos' => 'nullable|string|max:500',
            'datosNegocio.tiene_medios_pago' => 'nullable|boolean',
            'datosNegocio.descripcion_medios_pago' => 'nullable|string|max:500',
            'datosNegocio.modalidad_atencion' => 'nullable|string|max:100',
            'datosNegocio.restriccion_actual' => 'nullable|string|max:500',
            'datosNegocio.cuenta_con_ahorros' => 'nullable|boolean',
            'datosNegocio.ahorros_sustentables' => 'nullable|boolean',
            'datosNegocio.fecha_ultima_compra' => 'nullable|date',
            'datosNegocio.monto_ultima_compra' => 'nullable|numeric|min:0',
            'datosNegocio.variacion_compras_mes_anterior' => 'nullable|string|max:50',
            'datosNegocio.cuentas_por_cobrar_monto' => 'nullable|numeric|min:0',
            'datosNegocio.cuentas_por_cobrar_num_clientes' => 'nullable|integer|min:0',
            'datosNegocio.tiempo_recuperacion' => 'nullable|string|max:100',
            'datosNegocio.foto_apuntes_cobranza' => 'nullable|string|max:255',
            'datosNegocio.detalle_activo_fijo' => 'nullable|string|max:500',
            'datosNegocio.valor_actual_activo_fijo' => 'nullable|numeric|min:0',
            'datosNegocio.foto_activo_fijo' => 'nullable|string|max:255',
            'datosNegocio.dias_efectivo'   => 'nullable|integer|min:0',
            'datosNegocio.pagos_realizados_mes' => 'nullable|numeric|min:0',
            'datosNegocio.gastos_administrativos_fijos' => 'nullable|numeric|min:0',
            'datosNegocio.gastos_operativos_variables' => 'nullable|numeric|min:0',
            'datosNegocio.imprevistos_mermas' => 'nullable|numeric|min:0',
            'datosNegocio.promedio_ventas_pdt' => 'nullable|numeric|min:0',
            'datosNegocio.contribucion_essalud_anual' => 'nullable|numeric|min:0',
            'datosNegocio.referencias_comerciales' => 'nullable|string|max:500',
            // Permitimos array para que pase al action:
            'datosNegocio.*'               => 'nullable', 

            // Inventario dentro de negocio - AGREGADAS VALIDACIONES PARA CAMPOS FALTANTES
            'datosNegocio.detalleInventario'   => 'nullable|array',
            'datosNegocio.detalleInventario.*.nombre_producto' => 'required_with:datosNegocio.detalleInventario|string|max:150',
            'datosNegocio.detalleInventario.*.unidad_medida' => 'nullable|string|max:50',
            'datosNegocio.detalleInventario.*.precio_compra_unitario' => 'nullable|numeric|min:0',
            'datosNegocio.detalleInventario.*.precio_venta_unitario' => 'nullable|numeric|min:0',
            'datosNegocio.detalleInventario.*.margen_ganancia' => 'nullable|numeric|min:0|max:100',
            'datosNegocio.detalleInventario.*.cantidad_inventario' => 'nullable|numeric|min:0',
            'datosNegocio.detalleInventario.*.precio_total_estimado' => 'nullable|numeric|min:0',
            'datosNegocio.detalleInventario.*.created_at' => 'nullable|date',
            'datosNegocio.detalleInventario.*.updated_at' => 'nullable|date',

            // ==========================================
            // 4. UNIDAD FAMILIAR
            // ==========================================
            'unidadFamiliar'               => 'nullable|array',
            'unidadFamiliar.gastos_alimentacion' => 'nullable|numeric|min:0',
            'unidadFamiliar.numero_miembros'     => 'nullable|integer|min:1',
            'unidadFamiliar.gastos_educacion' => 'nullable|numeric|min:0',
            'unidadFamiliar.detalle_educacion' => 'nullable|string|max:500',
            'unidadFamiliar.gastos_servicios' => 'nullable|numeric|min:0',
            'unidadFamiliar.gastos_movilidad' => 'nullable|numeric|min:0',
            'unidadFamiliar.tiene_deudas_ifis' => 'nullable|boolean',
            'unidadFamiliar.ifi_1_nombre' => 'nullable|string|max:100',
            'unidadFamiliar.ifi_1_cuota' => 'nullable|numeric|min:0',
            'unidadFamiliar.ifi_2_nombre' => 'nullable|string|max:100',
            'unidadFamiliar.ifi_2_cuota' => 'nullable|numeric|min:0',
            'unidadFamiliar.ifi_3_nombre' => 'nullable|string|max:100',
            'unidadFamiliar.ifi_3_cuota' => 'nullable|numeric|min:0',
            'unidadFamiliar.gastos_salud' => 'nullable|numeric|min:0',
            'unidadFamiliar.frecuencia_salud' => 'nullable|string|max:50',
            'unidadFamiliar.detalle_salud' => 'nullable|string|max:500',
            'unidadFamiliar.total_gastos_mensuales' => 'nullable|numeric|min:0',
            'unidadFamiliar.*'             => 'nullable',

            // ==========================================
            // 5. GARANTÍAS
            // ==========================================
            'garantias'                    => 'nullable|array',
            'garantias.*.descripcion_bien' => 'required_with:garantias|string|max:500',
            'garantias.*.valor_comercial'  => 'nullable|numeric|min:0',
            'garantias.*.valor_realizacion'=> 'nullable|numeric|min:0',
            'garantias.*.es_declaracion_jurada' => 'nullable|boolean',
            'garantias.*.moneda'           => 'nullable|string|max:10',
            'garantias.*.clase_garantia'   => 'nullable|string|max:100',
            'garantias.*.documento_garantia' => 'nullable|string|max:100',
            'garantias.*.tipo_garantia'    => 'nullable|string|max:100',
            'garantias.*.direccion_bien'   => 'nullable|string|max:255',
            'garantias.*.monto_garantia'   => 'nullable|numeric|min:0',
            'garantias.*.ficha_registral'  => 'nullable|string|max:100',
            'garantias.*.fecha_ultima_valuacion' => 'nullable|date',
            'garantias.*.created_at'       => 'nullable|date',
            'garantias.*.updated_at'       => 'nullable|date',
            'garantias.*'                  => 'nullable',

            // ==========================================
            // 6. AVAL
            // ==========================================
            'aval.dniAval'                 => 'sometimes|nullable|digits_between:8,9',
            'aval.apellidoPaternoAval'     => 'sometimes|nullable|string|max:100',
            'aval.apellidoMaternoAval'     => 'sometimes|nullable|string|max:100',
            'aval.nombresAval'             => 'sometimes|nullable|string|max:100',
            'aval.telefonoFijoAval'        => 'sometimes|nullable|digits_between:6,9',
            'aval.telefonoMovilAval'       => 'sometimes|nullable|digits:9',
            'aval.direccionAval'           => 'sometimes|nullable|string|max:255',
            'aval.referenciaDomicilioAval' => 'sometimes|nullable|string|max:255',
            'aval.provinciaAval'           => 'sometimes|nullable|string|max:100',
            'aval.departamentoAval'        => 'sometimes|nullable|string|max:100',
            'aval.distritoAval'            => 'sometimes|nullable|string|max:100',
            'aval.relacionClienteAval'     => 'sometimes|nullable|string|max:50',
        ];
    }
}