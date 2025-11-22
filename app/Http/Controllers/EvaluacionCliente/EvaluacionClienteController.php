<?php

namespace App\Http\Controllers\EvaluacionCliente;

use App\Http\Controllers\Controller;
use App\Http\Requests\EvaluacionClienteRequest\CorrectEvaluacionRequest;
use App\Models\EvaluacionCliente;
use Illuminate\Http\JsonResponse;

// Actions
use App\Http\Controllers\EvaluacionCliente\utilities\StoreEvaluacionAction;
use App\Http\Controllers\EvaluacionCliente\utilities\UpdateEvaluacionAction;
use App\Http\Controllers\EvaluacionCliente\utilities\UpdateEvaluacionStatusAction;

// Services
use App\Http\Controllers\EvaluacionCliente\services\BuscarEvaluacionService;

// Form Requests
use App\Http\Requests\EvaluacionClienteRequest\IndexEvaluacionClienteRequest;
use App\Http\Requests\EvaluacionClienteRequest\StoreEvaluacionClienteRequest;
use App\Http\Requests\EvaluacionClienteRequest\UpdateEvaluacionClienteRequest;
use App\Http\Requests\EvaluacionClienteRequest\UpdateEvaluacionStatusRequest;

class EvaluacionClienteController extends Controller
{
    /**
     * Busca evaluaciones por DNI de cliente.
     */
    public function index(IndexEvaluacionClienteRequest $request, BuscarEvaluacionService $finder): JsonResponse
    {
        // La autorización y validación del DNI ya ocurrieron en el Form Request.
        $evaluaciones = $finder->findByDni(
            $request->validated('dni'),
            $request->user()
        );

        return response()->json($evaluaciones);
    }

    /**
     * Almacena una nueva evaluación de cliente.
     */
    public function store(StoreEvaluacionClienteRequest $request, StoreEvaluacionAction $action): JsonResponse
    {

        $resultado = $action->handle($request->validated(), $request);

        if ($resultado['success']) {
            return response()->json([
                'msg' => $resultado['message'], 
                'usuario_id' => $resultado['usuario_id']
            ], 201);
        }

        return response()->json(['msg' => $resultado['message']], 500);
    }

    /**
     * Actualiza una evaluación y los datos del cliente.
     */
    public function update(UpdateEvaluacionClienteRequest $request, int $evaluacionId, UpdateEvaluacionAction $action): JsonResponse
    {
        $resultado = $action->handle($request->validated(), $evaluacionId);

        if ($resultado['success']) {
            return response()->json(['msg' => $resultado['message']]);
        }
        
        return response()->json(['msg' => $resultado['message']], 500);
    }

     /**
     * Corrige los datos de una evaluación crediticia específica.
     *
     * @param CorrectEvaluacionRequest $request
     * @param EvaluacionCliente $evaluacion El ID de la evaluación se inyecta automáticamente.
     * @return JsonResponse
     */
    public function correctEvaluation(CorrectEvaluacionRequest $request, EvaluacionCliente $evaluacion): JsonResponse
    {
        // 1. Los datos ya vienen validados por CorrectEvaluacionRequest.
        $validatedData = $request->validated();

        // 2. Actualiza la evaluación con los datos validados.
        $evaluacion->update($validatedData);

        // 3. Devuelve una respuesta exitosa.
        return response()->json([
            'message' => 'Evaluación corregida exitosamente.',
            'evaluacion' => $evaluacion // Opcional: devuelve los datos actualizados.
        ], 200);
    }
    
    /**
     * Actualiza el estado de una evaluación (Aprobar/Rechazar).
     */
    public function updateStatus(UpdateEvaluacionStatusRequest $request, int $evaluacionId, UpdateEvaluacionStatusAction $action): JsonResponse
    {
        // La autorización (solo jefe de negocios) y la validación ya ocurrieron.
        $resultado = $action->handle($evaluacionId, $request->validated());

        if ($resultado['success']) {
            return response()->json(['msg' => $resultado['message']]);
        }

        // Devolvemos el código de estado que la Action nos sugiere.
        $statusCode = $resultado['status_code'] ?? 500;
        return response()->json(['msg' => $resultado['message']], $statusCode);
    }
}