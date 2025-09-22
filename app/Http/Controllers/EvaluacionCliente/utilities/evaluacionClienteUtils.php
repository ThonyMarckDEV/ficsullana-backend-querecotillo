<?php

namespace App\Http\Controllers\EvaluacionCliente\utilities;

use Illuminate\Support\Facades\Validator;

class EvaluacionClienteUtils
{
    /**
     * Valida los datos de la solicitud de préstamo
     *
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public static function StoreValidate(array $data)
    {
        return Validator::make($data, [
            // Bloque usuario
            'usuario.nombres'          => 'required|string|max:100',
            'usuario.apellidoPaterno'  => 'required|string|max:100',
            'usuario.apellidoMaterno'  => 'nullable|string|max:100',
            'usuario.dni'              => 'required|digits:9',

            // Bloque crédito
            'credito.montoPrestamo'    => 'required|numeric|min:100',
            'credito.plazoMeses'       => 'required|integer|min:1',
            'credito.tasaInteres'      => 'required|numeric|min:0',

        ]);
    }
}
