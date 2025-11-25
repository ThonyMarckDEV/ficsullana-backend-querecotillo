<?php

namespace App\Http\Controllers\EvaluacionCliente;

use App\Http\Controllers\Controller;
use App\Http\Controllers\EvaluacionCliente\services\FileStorageService;
use App\Http\Requests\EvaluacionClienteRequest\CorrectEvaluacionRequest;
use App\Models\EvaluacionCliente;
use Illuminate\Http\JsonResponse;

// Actions
use App\Http\Controllers\EvaluacionCliente\utilities\StoreEvaluacionAction;
use App\Http\Controllers\EvaluacionCliente\utilities\UpdateEvaluacionAction;
use App\Http\Controllers\EvaluacionCliente\utilities\UpdateEvaluacionStatusAction;

// Services
use App\Http\Controllers\EvaluacionCliente\services\BuscarEvaluacionService;
use App\Http\Controllers\EvaluacionCliente\utilities\ShowEvaluacionAction;
// Form Requests
use App\Http\Requests\EvaluacionClienteRequest\IndexEvaluacionClienteRequest;
use App\Http\Requests\EvaluacionClienteRequest\StoreEvaluacionClienteRequest;
use App\Http\Requests\EvaluacionClienteRequest\UpdateEvaluacionClienteRequest;
use App\Http\Requests\EvaluacionClienteRequest\UpdateEvaluacionStatusRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EvaluacionClienteController extends Controller
{

    protected $fileStorage;

    // Inyectamos el servicio en el constructor
    public function __construct(FileStorageService $fileStorage)
    {
        $this->fileStorage = $fileStorage;
    }

    /**
     * Lista evaluaciones.
     * - Si llega 'dni', filtra.
     * - Si no llega, lista todo (según rol).
     */
    public function index(IndexEvaluacionClienteRequest $request, BuscarEvaluacionService $finder): JsonResponse
    {
        // Obtenemos el 'dni' validado (ahora puede ser null)
        $dni = $request->validated('dni');

        $evaluaciones = $finder->findByDni(
            $dni, 
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

    public function update(UpdateEvaluacionClienteRequest $request, int $evaluacionId, UpdateEvaluacionAction $action): JsonResponse
    {
        // --- ASÍ DEBE QUEDAR LA PARTE DE LOS LOGS ---
        Log::info("=== REQUEST UPDATE {$evaluacionId} ===");
        
        // CORRECCIÓN: Quitamos ->toArray() porque allFiles() ya es un array
        Log::info('All files received:', $request->allFiles()); 
        
        Log::info('All data received:', $request->all());

        $resultado = $action->handle($request->validated(), $evaluacionId);

        if ($resultado['success']) {
            return response()->json(['msg' => $resultado['message']]);
        }
        
        return response()->json(['msg' => $resultado['message']], 500);
    }

   /**
     * Muestra el detalle de una evaluación específica.
     */
    public function show(int $id, ShowEvaluacionAction $showAction): JsonResponse
    {
        // Delegamos toda la lógica al Action
        $evaluacion = $showAction->handle($id);

        if (!$evaluacion) {
            return response()->json(['message' => 'Evaluación no encontrada'], 404);
        }

        return response()->json($evaluacion, 200);
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