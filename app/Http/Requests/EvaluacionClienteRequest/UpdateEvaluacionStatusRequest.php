<?php

namespace App\Http\Requests\EvaluacionClienteRequest;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEvaluacionStatusRequest extends FormRequest
{

    /**
     * Obtiene las reglas de validación que se aplican a la solicitud.
     */
    public function rules(): array
    {
        return [
            'estado'        => 'required|in:1,2', // 1: Aprobado, 2: Rechazado
            'observaciones' => 'nullable|string|required_if:estado,2|max:500',
        ];
    }
    
}