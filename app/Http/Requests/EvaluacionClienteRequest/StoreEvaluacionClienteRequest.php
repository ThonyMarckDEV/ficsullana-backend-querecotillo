<?php

namespace App\Http\Requests\EvaluacionClienteRequest;

use Illuminate\Foundation\Http\FormRequest;

class StoreEvaluacionClienteRequest extends FormRequest
{
    /**
     * Prepara los datos para la validación.
     * Esta función es la solución clave: decodifica el JSON enviado desde el frontend
     * antes de que Laravel intente validar los datos.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('data')) {
            $decodedData = json_decode($this->input('data'), true);
            if (is_array($decodedData)) {
                $this->merge($decodedData);
            }
        }
    }

    /**
     * Obtiene las reglas de validación que se aplican a la solicitud.
     */
    public function rules(): array
    {
        return [
            // REGLAS PARA EL ARCHIVO PDF
            'pdf'                                 => 'required|file|mimes:pdf|max:2048', // Límite de 2MB

            // CAMPOS USUARIO
            'usuario.dni'                         => 'required|digits_between:8,9',
            'usuario.apellidoPaterno'             => 'required|string|max:100',
            'usuario.apellidoMaterno'             => 'nullable|string|max:100',
            'usuario.nombre'                      => 'required|string|max:100',
            'usuario.fechaNacimiento'             => 'required|date',
            'usuario.fechaCaducidadDni'           => 'required|date',
            'usuario.sexo'                        => 'required|string|max:50',
            'usuario.estadoCivil'                 => 'required|string|max:50',
            'usuario.nacionalidad'                => 'required|string|max:50',
            'usuario.residePeru'                  => 'required|boolean',
            'usuario.nivelEducativo'              => 'nullable|string|max:100',
            'usuario.profesion'                   => 'nullable|string|max:100',
            'usuario.telefonoFijo'                => 'nullable|digits_between:6,9',
            'usuario.telefonoMovil'               => 'required|digits:9',
            'usuario.correo'                      => 'required|email|max:150',
            'usuario.enfermedadesPreexistentes'   => 'required|boolean',
            'usuario.ctaAhorros'                  => 'required|string|max:50',
            'usuario.entidadFinanciera'           => 'required|string|max:100',
            'usuario.direccionFiscal'             => 'required|string|max:255',
            'usuario.direccionCorrespondencia'    => 'nullable|string|max:255',
            'usuario.tipoVivienda'                => 'required|string|max:50',
            'usuario.tiempoResidencia'            => 'nullable|string|max:50',
            'usuario.referenciaDomicilio'         => 'nullable|string|max:255',
            'usuario.centroLaboral'               => 'nullable|string|max:150',
            'usuario.ingresoMensual'              => 'required|numeric|min:0',
            'usuario.inicioLaboral'               => 'nullable|date',
            'usuario.situacionLaboral'            => 'required|string|max:50',
            'usuario.provincia'                   => 'required|string|max:100',
            'usuario.departamento'                => 'required|string|max:100',
            'usuario.distrito'                    => 'required|string|max:100',
            'usuario.expuestaPoliticamente'       => 'required|boolean',

            // CAMPOS CRÉDITO
            'credito.producto'                    => 'required|string|max:100',
            'credito.montoPrestamo'               => 'required|numeric|min:100',
            'credito.tasaInteres'                 => 'required|numeric|min:0|max:100',
            'credito.cuotas'                      => 'required|integer|min:1',
            'credito.modalidadCredito'            => 'required|string|max:50',
            'credito.destinoCredito'              => 'required|string|max:150',
            'credito.periodoCredito'              => 'required|string|max:50',
            
            // CAMPOS AVAL (usando 'sometimes' para validar solo si el objeto 'aval' está presente)
            'aval'                                => 'sometimes|present|array',
            'aval.dniAval'                        => 'required_with:aval|digits_between:8,9',
            'aval.apellidoPaternoAval'            => 'required_with:aval|string|max:100',
            'aval.apellidoMaternoAval'            => 'nullable|string|max:100',
            'aval.nombresAval'                    => 'required_with:aval|string|max:100',
            'aval.telefonoFijoAval'               => 'nullable|digits_between:6,9',
            'aval.telefonoMovilAval'              => 'required_with:aval|digits:9',
            'aval.direccionAval'                  => 'required_with:aval|string|max:255',
            'aval.referenciaDomicilioAval'        => 'nullable|string|max:255',
            'aval.provinciaAval'                  => 'required_with:aval|string|max:100',
            'aval.departamentoAval'               => 'required_with:aval|string|max:100',
            'aval.distritoAval'                   => 'required_with:aval|string|max:100',
            'aval.relacionClienteAval'            => 'required_with:aval|string|max:50',
        ];
    }

    /**
     * Obtiene los mensajes de error personalizados para las reglas de validación.
     */
    public function messages(): array
    {
        return [
            // Mensajes generales
            'required' => 'El campo :attribute es obligatorio.',
            'string'   => 'El campo :attribute debe ser texto.',
            'numeric'  => 'El campo :attribute debe ser un número.',
            'digits'   => 'El campo :attribute debe tener :digits dígitos.',
            'digits_between' => 'El campo :attribute debe tener entre :min y :max dígitos.',
            'boolean'  => 'El campo :attribute debe ser verdadero o falso.',
            'email'    => 'El campo :attribute debe ser un correo electrónico válido.',
            'date'     => 'El campo :attribute no es una fecha válida.',
            'min'      => 'El valor de :attribute debe ser de al menos :min.',
            'max'      => 'El valor de :attribute no debe superar :max.',

            // Mensajes específicos para el PDF
            'pdf.required' => 'Es obligatorio adjuntar el archivo de evaluación.',
            'pdf.mimes'    => 'El archivo debe ser un PDF.',
            'pdf.max'      => 'El PDF no debe pesar más de 2MB.',

            // Mensajes para campos específicos del usuario
            'usuario.dni.required' => 'El DNI del cliente es obligatorio.',
            'usuario.telefonoMovil.digits' => 'El teléfono móvil debe tener 9 dígitos.',
            
            // Mensajes para campos específicos del crédito
            'credito.montoPrestamo.min' => 'El monto del préstamo debe ser de al menos S/ 100.',
        ];
    }
}