<?php

namespace App\Actions\EvaluacionCliente;

use App\Models\EvaluacionCliente;
use App\Services\EvaluacionCliente\AvalService;
use App\Services\EvaluacionCliente\ClienteDataService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class UpdateEvaluacionAction
{
    public function __construct(
        protected ClienteDataService $clienteService,
        protected AvalService $avalService
    ) {}

    public function handle(array $data, int $evaluacionId): array
    {
        try {
            DB::transaction(function () use ($data, $evaluacionId) {
                // 1. Usa el servicio para actualizar al cliente y sus datos
                $usuario = $this->clienteService->createOrUpdate($data['usuario']);

                // 2. Usa el servicio para gestionar el aval
                $this->avalService->manage($usuario, $data['aval'] ?? null);
                
                // 3. La lógica específica de esta acción: Actualizar la evaluación
                $evaluacion = EvaluacionCliente::findOrFail($evaluacionId);
                $evaluacion->update([
                    ...$data['credito'],
                    'estado'        => 0,      // Vuelve a PENDIENTE
                    'observaciones' => null, // Limpia observaciones
                ]);
            });

            return ['success' => true, 'message' => 'Evaluación corregida y enviada exitosamente.'];
        } catch (Throwable $e) {
            Log::error("Error en UpdateEvaluacionAction para evaluacion ID {$evaluacionId}: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al guardar los cambios.'];
        }
    }
}