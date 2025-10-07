<?php

namespace App\Http\Requests\EvaluacionClienteRequest;

use Illuminate\Foundation\Http\FormRequest;

class CorrectEvaluacionRequest extends FormRequest
{

    /**
     * Obtiene las reglas de validación que se aplican a la solicitud.
     */
    public function rules(): array
    {
        return [
            'producto'         => 'required|string|max:255',
            'montoPrestamo'    => 'required|numeric|min:0',
            'tasaInteres'      => 'required|integer|min:0',
            'cuotas'           => 'required|integer|min:1',
            'modalidadCredito' => 'required|string|max:255',
            'destinoCredito'   => 'required|string|max:255',
            'periodoCredito'   => 'required|string|max:255',
        ];
    }
}