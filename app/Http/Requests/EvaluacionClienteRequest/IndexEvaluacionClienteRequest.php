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
            'dni'          => 'nullable|string|max:20',
            'fecha_inicio' => 'nullable|date', // Nueva regla
            'fecha_fin'    => 'nullable|date|after_or_equal:fecha_inicio', // Nueva regla
        ];
    }
}