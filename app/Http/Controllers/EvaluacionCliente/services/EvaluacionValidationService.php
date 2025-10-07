<?php

namespace App\Http\Controllers\EvaluacionCliente\services;

use App\Models\Datos;
use App\Models\EvaluacionCliente;

class EvaluacionValidationService
{
    /**
     * Verifica si un cliente existente ya tiene una evaluación que impida crear una nueva.
     *
     * @param int $datosId
     * @return array ['passes' => bool, 'message' => ?string]
     */
    public function checkExisting(int $datosId): array
    {
        $datos = Datos::find($datosId);

        if ($datos && $datos->usuario) {
            $evaluacionExistente = EvaluacionCliente::where('id_Cliente', $datos->usuario->id)
                ->whereIn('estado', [0, 2]) // 0: Pendiente, 2: Rechazado
                ->exists();

            if ($evaluacionExistente) {
                return [
                    'passes' => false,
                    'message' => 'El cliente ya tiene una evaluación pendiente o rechazada.'
                ];
            }
        }

        return ['passes' => true, 'message' => null];
    }
}