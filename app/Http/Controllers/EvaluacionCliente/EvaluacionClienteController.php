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
use Illuminate\Support\Str;

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
        // 1. Obtener los datos validados
        $dni = $request->validated('dni');
        $fechaInicio = $request->validated('fecha_inicio'); // <--- Nuevo
        $fechaFin = $request->validated('fecha_fin');       // <--- Nuevo

        // 2. Pasar los 4 argumentos al servicio
        $evaluaciones = $finder->findByDni(
            $dni,           // 1. DNI
            $fechaInicio,   // 2. Fecha Inicio
            $fechaFin,      // 3. Fecha Fin
            $request->user() // 4. Usuario
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


       /**
     * Obtiene las firmas en Base64 directamente del DISCO (File System).
     * Ignora la BD y busca en la estructura de carpetas definida.
     */
    public function getFirmas(int $id): JsonResponse
    {
        // 1. Necesitamos el ID del cliente para armar la ruta
        $evaluacion = EvaluacionCliente::select('id', 'id_Cliente')->find($id);

        if (!$evaluacion) {
            return response()->json(['message' => 'Evaluación no encontrada'], 404);
        }

        $clienteId = $evaluacion->id_Cliente;
        $evaluacionId = $evaluacion->id;

        // 2. Definir las carpetas según tu estructura
        $pathClienteFolder = "clientes/{$clienteId}/evaluaciones/{$evaluacionId}/firma-cliente";
        $pathAvalFolder = "clientes/{$clienteId}/evaluaciones/{$evaluacionId}/firma-aval";

        // 3. Helper para buscar el archivo en la carpeta y convertir a Base64
        $buscarYConvertir = function ($folderPath) {
            // Escaneamos la carpeta (porque el nombre tiene timestamp aleatorio)
            $files = Storage::disk('public')->files($folderPath);

            // Si no hay archivos, retornamos null
            if (empty($files)) {
                return null;
            }

            // Tomamos el primer archivo encontrado (asumiendo que limpias los viejos)
            $filePath = $files[0];
            
            // Obtenemos ruta absoluta para leer el contenido
            $fullPath = Storage::disk('public')->path($filePath);

            if (!file_exists($fullPath)) {
                return null;
            }

            // Leemos y codificamos
            $fileData = file_get_contents($fullPath);
            $mimeType = mime_content_type($fullPath);
            $base64 = base64_encode($fileData);

            return 'data:' . $mimeType . ';base64,' . $base64;
        };

        return response()->json([
            'firma_cliente' => $buscarYConvertir($pathClienteFolder),
            'firma_aval'    => $buscarYConvertir($pathAvalFolder),
        ]);
    }
}