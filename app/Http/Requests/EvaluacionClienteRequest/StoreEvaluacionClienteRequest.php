<?php

namespace App\Http\Requests\EvaluacionClienteRequest;

use Illuminate\Foundation\Http\FormRequest;

class StoreEvaluacionClienteRequest extends FormRequest
{

    /**
     * Obtiene las reglas de validación que se aplican a la solicitud.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Simplemente retorna el array de reglas.
        // Laravel usará los datos de la solicitud ('request') para validarlos.
        return [
            // CAMPOS USUARIO
            'usuario.dni'                       => 'required|digits_between:8,9',
            'usuario.apellidoPaterno'           => 'required|string|max:100',
            'usuario.apellidoMaterno'           => 'nullable|string|max:100',
            'usuario.nombre'                    => 'required|string|max:100',
            'usuario.fechaNacimiento'           => 'required|date',
            'usuario.sexo'                      => 'required|string|max:50',
            'usuario.estadoCivil'               => 'required|string|max:50',
            'usuario.nacionalidad'              => 'required|string|max:50',
            'usuario.residePeru'                => 'required|boolean',
            'usuario.nivelEducativo'            => 'nullable|string|max:100',
            'usuario.profesion'                 => 'nullable|string|max:100',
            'usuario.telefonoFijo'              => 'nullable|digits_between:6,9',
            'usuario.telefonoMovil'             => 'required|digits:9',
            'usuario.correo'                    => 'required|email|max:150',
            'usuario.enfermedadesPreexistentes' => 'required|boolean',
            'usuario.ctaAhorros'                => 'required|string|max:50',
            'usuario.entidadFinanciera'         => 'required|string|max:100',
            'usuario.direccionFiscal'           => 'required|string|max:255',
            'usuario.direccionCorrespondencia'  => 'nullable|string|max:255',
            'usuario.tipoVivienda'              => 'nullable|string|max:50',
            'usuario.tiempoResidencia'          => 'nullable|string|max:50',
            'usuario.referenciaDomicilio'       => 'nullable|string|max:255',
            'usuario.centroLaboral'             => 'nullable|string|max:150',
            'usuario.ingresoMensual'            => 'required|numeric|min:0',
            'usuario.inicioLaboral'             => 'nullable|date',
            'usuario.situacionLaboral'          => 'required|string|max:50',
            'usuario.provincia'                 => 'required|string|max:100',
            'usuario.departamento'              => 'required|string|max:100',
            'usuario.distrito'                  => 'required|string|max:100',
            'usuario.expuestaPoliticamente'     => 'required|boolean',

            // CAMPOS CRÉDITO
            'credito.producto'                  => 'required|string|max:100',
            'credito.montoPrestamo'             => 'required|numeric|min:100',
            'credito.tasaInteres'               => 'required|numeric|min:0|max:100',
            'credito.cuotas'                    => 'required|integer|min:1',
            'credito.modalidadCredito'          => 'required|string|max:50',
            'credito.destinoCredito'            => 'required|string|max:150',
            'credito.periodoCredito'            => 'required|string|max:50',
            
            // CAMPOS AVAL (solo si existe el bloque aval en el request)
            'aval.dniAval'                      => 'sometimes|required|digits_between:8,9',
            'aval.apellidoPaternoAval'          => 'sometimes|required|string|max:100',
            'aval.apellidoMaternoAval'          => 'sometimes|nullable|string|max:100',
            'aval.nombresAval'                  => 'sometimes|required|string|max:100',
            'aval.telefonoFijoAval'             => 'sometimes|nullable|digits_between:6,9',
            'aval.telefonoMovilAval'            => 'sometimes|required|digits:9',
            'aval.direccionAval'                => 'sometimes|required|string|max:255',
            'aval.referenciaDomicilioAval'      => 'sometimes|nullable|string|max:255',
            'aval.provinciaAval'                => 'sometimes|required|string|max:100',
            'aval.departamentoAval'             => 'sometimes|required|string|max:100',
            'aval.distritoAval'                 => 'sometimes|required|string|max:100',
            'aval.relacionClienteAval'          => 'sometimes|required|string|max:50',
        ];
    }
}