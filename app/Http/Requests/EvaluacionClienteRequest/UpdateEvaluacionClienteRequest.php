<?php

namespace App\Http\Requests\EvaluacionClienteRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // Asegúrate de que esta línea de importación exista

class UpdateEvaluacionClienteRequest extends FormRequest
{

    /**
     * Obtiene las reglas de validación que se aplican a la solicitud.
     */
    public function rules(): array
    {
        // Obtenemos el ID del usuario directamente desde los datos que manda el formulario.
        // Si no viene el 'id' en el array 'usuario', será null.
        $usuarioId = $this->input('usuario.id', null);

        return [
            // =======================================================================
            // ESTA ES LA PARTE CLAVE QUE SOLUCIONA TU PROBLEMA
            // =======================================================================

            // 1. VALIDAMOS EL ID:
            // Le decimos a Laravel: "El 'id' del usuario es obligatorio, debe ser un número,
            // y tiene que existir en la tabla 'datos'".
            // Al hacer esto, el ID ya no será eliminado de los datos validados.
            'usuario.id' => 'required|integer|exists:datos,id',

            // 2. VALIDAMOS EL DNI DE FORMA INTELIGENTE:
            // Le decimos: "El DNI es único, PERO ignora esta regla para el usuario
            // que estamos editando ($usuarioId)".
            // Esto permite guardar sin que bote error por su propio DNI.
            'usuario.dni' => [
                'required',
                'digits_between:8,9',
                Rule::unique('datos', 'dni')->ignore($usuarioId)
            ],

            // =======================================================================
            // El resto de las reglas se mantienen como las tenías
            // =======================================================================

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

            // CAMPOS CRÉDITO
            'credito.producto'             => 'required|string|max:100',
            'credito.montoPrestamo'        => 'required|numeric|min:100',
            'credito.tasaInteres'          => 'required|numeric|min:0|max:100',
            'credito.cuotas'               => 'required|integer|min:1',
            'credito.modalidadCredito'     => 'required|string|max:50',
            'credito.destinoCredito'       => 'required|string|max:150',
            'credito.periodoCredito'       => 'required|string|max:50',
            
            // CAMPOS AVAL (solo si existe el bloque aval en el request)
            'aval.dniAval'                 => 'sometimes|required|digits_between:8,9',
            'aval.apellidoPaternoAval'     => 'sometimes|required|string|max:100',
            'aval.apellidoMaternoAval'     => 'sometimes|nullable|string|max:100',
            'aval.nombresAval'             => 'sometimes|required|string|max:100',
            'aval.telefonoFijoAval'        => 'sometimes|nullable|digits_between:6,9',
            'aval.telefonoMovilAval'       => 'sometimes|required|digits:9',
            'aval.direccionAval'           => 'sometimes|required|string|max:255',
            'aval.referenciaDomicilioAval' => 'sometimes|nullable|string|max:255',
            'aval.provinciaAval'           => 'sometimes|required|string|max:100',
            'aval.departamentoAval'        => 'sometimes|required|string|max:100',
            'aval.distritoAval'            => 'sometimes|required|string|max:100',
            'aval.relacionClienteAval'     => 'sometimes|required|string|max:50',
        ];
    }
}