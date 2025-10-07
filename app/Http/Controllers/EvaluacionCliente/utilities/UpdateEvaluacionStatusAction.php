<?php

namespace App\Http\Controllers\EvaluacionCliente\utilities;

use App\Models\EvaluacionCliente;
use Illuminate\Support\Facades\Log;
use Throwable;

class UpdateEvaluacionStatusAction
{
    public function handle(int $evaluacionId, array $data): array
    {
        try {
            $evaluacion = EvaluacionCliente::findOrFail($evaluacionId);

            // Regla de negocio: Solo se pueden procesar evaluaciones pendientes.
            if ($evaluacion->estado != 0) {
                return [
                    'success' => false,
                    'message' => 'Solo las evaluaciones pendientes pueden ser aprobadas o rechazadas.',
                    'status_code' => 409 // Conflict
                ];
            }

            // Prepara los datos para la actualización
            $updateData = [
                'estado' => $data['estado'],
                'observaciones' => ($data['estado'] == 2) ? $data['observaciones'] : null,
            ];

            $evaluacion->update($updateData);

            return ['success' => true, 'message' => 'Estado de la evaluación actualizado exitosamente.'];

        } catch (Throwable $e) {
            Log::error("Error en UpdateEvaluacionStatusAction para ID {$evaluacionId}: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error interno al actualizar el estado.',
                'status_code' => 500
            ];
        }
    }
}