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

            // --- 3. AVAL (Solución al error: nullable|array) ---
            'aval'                        => 'nullable|array', 
            'aval.dniAval'                => 'required_with:aval.apellidoPaternoAval', // Solo valida si envías datos

            // --- 4. UNIDAD FAMILIAR ---
            'unidadFamiliar'                      => 'required|array',
            'unidadFamiliar.numero_miembros'      => 'required|integer|min:1',
            'unidadFamiliar.gastos_alimentacion'  => 'required|numeric|min:0',
            'unidadFamiliar.gastos_servicios'     => 'required|numeric|min:0',
            'unidadFamiliar.gastos_educacion'     => 'nullable|numeric',
            'unidadFamiliar.gastos_salud'         => 'nullable|numeric',

            // --- 5. DATOS NEGOCIO ---
            'datosNegocio'                        => 'required|array',
            'datosNegocio.ventas_diarias'         => 'required|numeric|min:0',
            'datosNegocio.monto_efectivo'         => 'required|numeric|min:0',
            'datosNegocio.monto_ultima_compra'    => 'required|numeric|min:0',
            'datosNegocio.detalleInventario'      => 'nullable|array', // Array de productos

            // --- 6. GARANTÍAS (Validación del Array) ---
            'garantias'                           => 'required|array|min:1', // Al menos una garantía
            'garantias.*.es_declaracion_jurada'   => 'required|boolean',    // Validar cada fila
            'garantias.*.clase_garantia'          => 'required|string',
            'garantias.*.valor_comercial'         => 'required|numeric|min:0',
            'garantias.*.moneda'                  => 'required|in:PEN,USD',

            // --- ARCHIVOS ---
            'firmaCliente'                => 'nullable',
            'firmaAval'                   => 'nullable',
        ];
    }
}