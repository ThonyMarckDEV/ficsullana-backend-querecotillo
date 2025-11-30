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

    public function __construct(FileStorageService $fileStorage)
    {
        $this->fileStorage = $fileStorage;
    }

    public function index(IndexEvaluacionClienteRequest $request, BuscarEvaluacionService $finder): JsonResponse
    {
        $dni = $request->validated('dni');
        $fechaInicio = $request->validated('fecha_inicio'); 
        $fechaFin = $request->validated('fecha_fin');     

        $evaluaciones = $finder->findByDni(
            $dni,           
            $fechaInicio,   
            $fechaFin,      
            $request->user() 
        );

        return response()->json($evaluaciones);
    }

    
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

        $resultado = $action->handle($request->validated(), $evaluacionId);

        if ($resultado['success']) {
            return response()->json(['msg' => $resultado['message']]);
        }
        
        return response()->json(['msg' => $resultado['message']], 500);
    }

    public function show(int $id, ShowEvaluacionAction $showAction): JsonResponse
    {
        $evaluacion = $showAction->handle($id);

        if (!$evaluacion) {
            return response()->json(['message' => 'Evaluación no encontrada'], 404);
        }

        return response()->json($evaluacion, 200);
    }


    public function correctEvaluation(CorrectEvaluacionRequest $request, EvaluacionCliente $evaluacion): JsonResponse
    {
        $validatedData = $request->validated();

        $evaluacion->update($validatedData);

        return response()->json([
            'message' => 'Evaluación corregida exitosamente.',
            'evaluacion' => $evaluacion
        ], 200);
    }
    

    public function updateStatus(UpdateEvaluacionStatusRequest $request, int $evaluacionId, UpdateEvaluacionStatusAction $action): JsonResponse
    {
        $resultado = $action->handle($evaluacionId, $request->validated());

        if ($resultado['success']) {
            return response()->json(['msg' => $resultado['message']]);
        }

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