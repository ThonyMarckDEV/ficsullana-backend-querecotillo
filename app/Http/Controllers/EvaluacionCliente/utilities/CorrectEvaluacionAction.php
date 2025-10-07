<?php

namespace App\Actions\EvaluacionCliente;

use App\Models\EvaluacionCliente;
use Illuminate\Support\Facades\Log;
use Throwable;

class CorrectEvaluacionAction
{
    public function handle(int $evaluacionId, array $data): array
    {
        try {
            $evaluacion = EvaluacionCliente::findOrFail($evaluacionId);

            // Regla de negocio: Solo se pueden corregir evaluaciones pendientes.
            if ($evaluacion->estado !== 0) {
                return [
                    'success' => false,
                    'message' => 'Solo se pueden editar evaluaciones que están pendientes.',
                    'status_code' => 409 // Conflict
                ];
            }
            
            $evaluacion->update($data);

            // Devolvemos la evaluación actualizada para refrescar el frontend
            return ['success' => true, 'message' => 'Evaluación corregida exitosamente.', 'evaluacion' => $evaluacion];

        } catch (Throwable $e) {
            Log::error("Error en CorrectEvaluacionAction para ID {$evaluacionId}: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error interno al guardar los cambios.',
                'status_code' => 500
            ];
        }
    }
}