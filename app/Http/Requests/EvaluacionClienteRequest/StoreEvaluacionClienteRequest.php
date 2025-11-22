<?php

namespace App\Http\Requests\EvaluacionClienteRequest;

use Illuminate\Foundation\Http\FormRequest;

class StoreEvaluacionClienteRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('data')) {
            $decodedData = json_decode($this->input('data'), true);
            if (is_array($decodedData)) {
                $this->merge($decodedData);
            }
        }
    }

    public function rules(): array
    {
        return [
            // --- 1. USUARIO ---
            'usuario.dni'                 => 'required|digits_between:8,9',
            'usuario.apellidoPaterno'     => 'required|string|max:100',
            'usuario.apellidoMaterno'     => 'nullable|string|max:100',
            'usuario.nombre'              => 'required|string|max:100',
            'usuario.fechaNacimiento'     => 'required|date',
            'usuario.fechaCaducidadDni'   => 'required|date',
            'usuario.nivelEducativo'      => 'nullable|string|max:100',
            'usuario.profesion'           => 'nullable|string|max:100',
            'usuario.sexo'                => 'required|string|max:50',
            'usuario.estadoCivil'         => 'required|string|max:50',
            'usuario.nacionalidad'        => 'required|string|max:50',
            'usuario.residePeru'          => 'required',
            'usuario.expuestaPoliticamente' => 'required',
            'usuario.enfermedadesPreexistentes' => 'required',
            'usuario.telefonoMovil'       => 'required|digits:9',
            'usuario.telefonoFijo'        => 'nullable|digits_between:6,9',
            'usuario.correo'              => 'required|email|max:150',
            'usuario.ctaAhorros'          => 'required|string|max:50',
            'usuario.entidadFinanciera'   => 'required|string|max:100',
            'usuario.direccionFiscal'     => 'required|string|max:255',
            'usuario.direccionCorrespondencia' => 'nullable|string|max:255',
            'usuario.tipoVivienda'        => 'required|string|max:50',
            'usuario.tiempoResidencia'    => 'nullable|string|max:50',
            'usuario.referenciaDomicilio' => 'nullable|string|max:255',
            'usuario.departamento'        => 'required|string|max:100',
            'usuario.provincia'           => 'required|string|max:100',
            'usuario.distrito'            => 'required|string|max:100',
            'usuario.centroLaboral'       => 'nullable|string|max:150',
            'usuario.ingresoMensual'      => 'required|numeric|min:0',
            'usuario.inicioLaboral'       => 'nullable|date',
            'usuario.situacionLaboral'    => 'required|string|max:50',

            // --- 2. CRÉDITO ---
            'credito.producto'            => 'required|string|max:100',
            'credito.montoPrestamo'       => 'required|numeric|min:100',
            'credito.tasaInteres'         => 'required|numeric',
            'credito.cuotas'              => 'required|integer|min:1',
            'credito.modalidadCredito'    => 'required|string',
            'credito.destinoCredito'      => 'required|string',
            'credito.periodoCredito'      => 'required|string',

            // --- 3. AVAL ---
            'aval'                        => 'nullable|array', 
            'aval.dniAval'                => 'required_with:aval.apellidoPaternoAval',
            // Agregar campos faltantes del aval si los usas (opcional pero recomendado)
            'aval.nombresAval'            => 'nullable|string',
            'aval.apellidoPaternoAval'    => 'nullable|string',
            'aval.apellidoMaternoAval'    => 'nullable|string',
            'aval.telefonoMovilAval'      => 'nullable|string',
            'aval.direccionAval'          => 'nullable|string',

            // --- 4. UNIDAD FAMILIAR ---
            'unidadFamiliar'                      => 'required|array',
            'unidadFamiliar.numero_miembros'      => 'required|integer|min:1',
            'unidadFamiliar.gastos_alimentacion'  => 'required|numeric|min:0',
            'unidadFamiliar.gastos_servicios'     => 'required|numeric|min:0',
            'unidadFamiliar.gastos_movilidad'     => 'nullable|numeric', // FALTABA
            'unidadFamiliar.gastos_educacion'     => 'nullable|numeric',
            'unidadFamiliar.detalle_educacion'    => 'nullable|string',  // FALTABA: Importante para que se guarde
            'unidadFamiliar.gastos_salud'         => 'nullable|numeric',
            'unidadFamiliar.frecuencia_salud'     => 'nullable|string',  // FALTABA
            'unidadFamiliar.detalle_salud'        => 'nullable|string',  // FALTABA
            'unidadFamiliar.tiene_deudas_ifis'    => 'nullable',         // FALTABA
            // IFIs
            'unidadFamiliar.ifi_1_nombre'         => 'nullable|string',
            'unidadFamiliar.ifi_1_cuota'          => 'nullable|numeric',
            'unidadFamiliar.ifi_2_nombre'         => 'nullable|string',
            'unidadFamiliar.ifi_2_cuota'          => 'nullable|numeric',
            'unidadFamiliar.ifi_3_nombre'         => 'nullable|string',
            'unidadFamiliar.ifi_3_cuota'          => 'nullable|numeric',


            // --- 5. DATOS NEGOCIO ---
            'datosNegocio'                                => 'required|array',
            // Campos monetarios existentes
            'datosNegocio.ventas_diarias'                 => 'required|numeric|min:0',
            'datosNegocio.monto_efectivo'                 => 'required|numeric|min:0',
            'datosNegocio.monto_ultima_compra'            => 'required|numeric|min:0',
            'datosNegocio.cuentas_por_cobrar_monto'       => 'nullable|numeric',
            'datosNegocio.valor_actual_activo_fijo'       => 'nullable|numeric',
            'datosNegocio.gastos_administrativos_fijos'   => 'nullable|numeric',
            'datosNegocio.gastos_operativos_variables'    => 'nullable|numeric',
            
            // --- CAMPOS TEXTO QUE FALTABAN Y SE GUARDABAN NULL ---
            'datosNegocio.otros_ingresos_monto'           => 'nullable|numeric',
            'datosNegocio.otros_ingresos_sector'          => 'nullable|string',
            'datosNegocio.otros_ingresos_tiempo'          => 'nullable|string',
            'datosNegocio.otros_ingresos_frecuencia'      => 'nullable|string',
            'datosNegocio.sustento_otros_ingresos'        => 'nullable|string',
            'datosNegocio.depende_otros_ingresos'         => 'nullable',
            
            'datosNegocio.zona_ubicacion'                 => 'nullable|string',
            'datosNegocio.modalidad_atencion'             => 'nullable|string',
            'datosNegocio.fecha_ultima_compra'            => 'nullable|date',
            'datosNegocio.variacion_compras_mes_anterior' => 'nullable|string',
            
            'datosNegocio.cuentas_por_cobrar_num_clientes'=> 'nullable|integer',
            'datosNegocio.tiempo_recuperacion'            => 'nullable|string',
            'datosNegocio.detalle_activo_fijo'            => 'nullable|string',
            'datosNegocio.referencias_comerciales'        => 'nullable|string',

            'datosNegocio.detalleInventario'              => 'nullable|array',
            // Validación del array inventario (opcional pero recomendada)
            'datosNegocio.detalleInventario.*.nombre_producto' => 'required_with:datosNegocio.detalleInventario|string',
            'datosNegocio.detalleInventario.*.precio_compra_unitario' => 'nullable|numeric',
            'datosNegocio.detalleInventario.*.precio_venta_unitario' => 'nullable|numeric',
            'datosNegocio.detalleInventario.*.cantidad_inventario' => 'nullable|numeric',
            'datosNegocio.detalleInventario.*.precio_total_estimado' => 'nullable|numeric',
            'datosNegocio.detalleInventario.*.unidad_medida' => 'nullable|string',


            // --- 6. GARANTÍAS ---
            'garantias'                           => 'required|array|min:1',
            'garantias.*.es_declaracion_jurada'   => 'required', // Acepta boolean o 0/1
            'garantias.*.moneda'                  => 'required|in:PEN,USD',
            'garantias.*.valor_comercial'         => 'required|numeric|min:0',
            // --- CAMPOS TEXTO QUE FALTABAN ---
            'garantias.*.clase_garantia'          => 'required|string',
            'garantias.*.documento_garantia'      => 'nullable|string',
            'garantias.*.tipo_garantia'           => 'nullable|string',
            'garantias.*.descripcion_bien'        => 'nullable|string',
            'garantias.*.direccion_bien'          => 'nullable|string',
            'garantias.*.valor_realizacion'       => 'nullable|numeric',
            'garantias.*.monto_garantia'          => 'nullable|numeric',
            'garantias.*.ficha_registral'         => 'nullable|string',
            'garantias.*.fecha_ultima_valuacion'  => 'nullable|date',

            // --- ARCHIVOS ---
            'firmaCliente'                => 'nullable',
            'firmaAval'                   => 'nullable',
            // Fotos negocio
            'fotoApuntesCobranza'         => 'nullable',
            'fotoActivoFijo'              => 'nullable',
        ];
    }
}