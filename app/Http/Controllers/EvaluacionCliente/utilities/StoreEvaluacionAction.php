<?php

namespace App\Http\Controllers\EvaluacionCliente\utilities;

use App\Http\Controllers\EvaluacionCliente\services\AvalService;
use App\Http\Controllers\EvaluacionCliente\services\ClienteDataService;
use App\Http\Controllers\EvaluacionCliente\services\EvaluacionValidationService;
use App\Models\EvaluacionCliente;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class StoreEvaluacionAction
{
    public function __construct(
        protected EvaluacionValidationService $validator,
        protected ClienteDataService $clienteService,
        protected AvalService $avalService
    ) {}

    public function handle(array $data): array
    {
        $usuarioData = $data['usuario'];

        // 1. Usa el servicio de validación previa , valida evaluaciones existentes
        if (isset($usuarioData['id']) && $usuarioData['id']) {
            $validation = $this->validator->checkExisting($usuarioData['id']);
            if (!$validation['passes']) {
                return ['success' => false, 'message' => $validation['message']];
            }
        }

        try {
            $usuarioId = DB::transaction(function () use ($data) {
                // 2. Usa el servicio para crear/actualizar al cliente y sus datos
                $usuario = $this->clienteService->createOrUpdate($data['usuario']);

                // 3. Usa el servicio para gestionar el aval
                $this->avalService->manage($usuario, $data['aval'] ?? null);

                // 4. La lógica específica de esta acción: Crear la evaluación
                EvaluacionCliente::create([
                    'id_Asesor'    => Auth::id(),
                    'id_Cliente'   => $usuario->id,
                    ...$data['credito'],
                    'estado'       => 0, // Pendiente
                ]);

                return $usuario->id;
            });

            return ['success' => true, 'message' => 'Evaluación creada exitosamente.', 'usuario_id' => $usuarioId];
        } catch (Throwable $e) {
            Log::error('Error en StoreEvaluacionAction: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Ocurrió un error al procesar la solicitud.'];
        }
    }
}