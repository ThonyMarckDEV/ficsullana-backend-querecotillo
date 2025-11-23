<?php

namespace App\Http\Requests\EvaluacionClienteRequest;

use Illuminate\Foundation\Http\FormRequest;

class IndexEvaluacionClienteRequest extends FormRequest
{
    /**
     * Obtiene las reglas de validación que se aplican a la solicitud.
     */
    public function rules(): array
    {
        return [
            // Cambiamos 'required' por 'nullable'.
            // Así, si el frontend no envía DNI, la validación pasa (para listar todo).
            'dni' => 'nullable|string|max:20', 
        ];
    }
}