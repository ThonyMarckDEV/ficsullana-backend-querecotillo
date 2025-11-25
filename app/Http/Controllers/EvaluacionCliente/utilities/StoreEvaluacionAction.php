<?php

namespace App\Http\Controllers\EvaluacionCliente\utilities;

use App\Http\Controllers\EvaluacionCliente\services\AvalService;
use App\Http\Controllers\EvaluacionCliente\services\ClienteDataService;
use App\Http\Controllers\EvaluacionCliente\services\EvaluacionValidationService;
use App\Http\Controllers\EvaluacionCliente\services\FileStorageService;
use App\Models\EvaluacionCliente;
use App\Models\UnidadFamiliar;
use App\Models\DatosNegocio;
use App\Models\DetalleInventarioNegocio;
use App\Models\Garantia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Throwable;

class StoreEvaluacionAction
{
    public function __construct(
        protected EvaluacionValidationService $validator,
        protected ClienteDataService $clienteService,
        protected AvalService $avalService,
        protected FileStorageService $fileStorage
    ) {}

    public function handle(array $validatedData, Request $request): array
    {
        $usuarioData = $validatedData['usuario'];

        // 1. Validación de existencia previa
        if (isset($usuarioData['id']) && $usuarioData['id']) {
            $validation = $this->validator->checkExisting($usuarioData['id']);
            if (!$validation['passes']) {
                return ['success' => false, 'message' => $validation['message']];
            }
        }

        try {
            $result = DB::transaction(function () use ($validatedData, $request) {
                
                // 2. Crear/Actualizar Cliente
                $usuario = $this->clienteService->createOrUpdate($validatedData['usuario']);

                // 3. Gestionar Aval
                $this->avalService->manage($usuario, $validatedData['aval'] ?? []);

                // 4. Crear Evaluación
                $evaluacion = EvaluacionCliente::create([
                    'id_Asesor'        => Auth::id(),
                    'id_Cliente'       => $usuario->id,
                    'producto'         => $validatedData['credito']['producto'],
                    'montoPrestamo'    => $validatedData['credito']['montoPrestamo'],
                    'tasaInteres'      => $validatedData['credito']['tasaInteres'],
                    'cuotas'           => $validatedData['credito']['cuotas'],
                    'modalidadCredito' => $validatedData['credito']['modalidadCredito'],
                    'destinoCredito'   => $validatedData['credito']['destinoCredito'],
                    'periodoCredito'   => $validatedData['credito']['periodoCredito'],
                    'estado'           => 0, // Pendiente
                ]);

                // 5. Guardar Unidad Familiar
                if (!empty($validatedData['unidadFamiliar'])) {
                    UnidadFamiliar::create([
                        'id_Evaluacion' => $evaluacion->id,
                        ...$validatedData['unidadFamiliar']
                    ]);
                }

                // 6. Guardar Datos del Negocio
                if (!empty($validatedData['datosNegocio'])) {
                    $negocioData = $validatedData['datosNegocio'];
                    
                    // Limpieza de fecha vacía
                    if (array_key_exists('fecha_ultima_compra', $negocioData) && empty($negocioData['fecha_ultima_compra'])) {
                        $negocioData['fecha_ultima_compra'] = null;
                    }

                    // --- MANEJO DE FOTOS DEL NEGOCIO (FÍSICO SOLAMENTE) ---
                    
                    // Foto Apuntes Cobranza
                    // Nombre del campo en el FormData frontend: 'fotoApuntesCobranza' (camelCase)
                    // O 'datosNegocio.foto_apuntes_cobranza' dependiendo de cómo lo envíes. 
                    // Asumiremos que viene en el request global o dentro del array validado como UploadedFile.
                    
                    // Verificamos si existe en el Request global o en validatedData
                    $fileCobranza = $request->file('datosNegocio.foto_apuntes_cobranza') ?? $request->file('fotoApuntesCobranza');
                    
                    if ($fileCobranza) {
                        $this->fileStorage->storeFile(
                            $fileCobranza,
                            $usuario->id,
                            $evaluacion->id,
                            'fotos-cobranza',         // Subfolder
                            'foto_apuntes_cobranza'   // Prefix
                        );
                    }
                    // IMPORTANTE: Quitamos el campo del array para que NO intente guardar ruta en BD
                    unset($negocioData['foto_apuntes_cobranza']);
                    $negocioData['foto_apuntes_cobranza'] = null; // Asegurar NULL en BD

                    // Foto Activo Fijo
                    $fileActivo = $request->file('datosNegocio.foto_activo_fijo') ?? $request->file('fotoActivoFijo');

                    if ($fileActivo) {
                        $this->fileStorage->storeFile(
                            $fileActivo,
                            $usuario->id,
                            $evaluacion->id,
                            'activo-fijo',            // Subfolder
                            'foto_activo_fijo'        // Prefix
                        );
                    }
                    // Quitamos del array
                    unset($negocioData['foto_activo_fijo']);
                    $negocioData['foto_activo_fijo'] = null; // Asegurar NULL en BD

                     // Foto Negocio (Si existiera)
                     $fileNegocio = $request->file('datosNegocio.foto_negocio') ?? $request->file('fotoNegocio');
                     if ($fileNegocio) {
                         $this->fileStorage->storeFile(
                             $fileNegocio,
                             $usuario->id,
                             $evaluacion->id,
                             'negocio',
                             'foto_negocio'
                         );
                     }
                     unset($negocioData['foto_negocio']);
                     $negocioData['foto_negocio'] = null;


                    // Crear registro en BD (sin rutas de archivos)
                    $datosNegocio = DatosNegocio::create([
                        'id_Evaluacion' => $evaluacion->id,
                        ...$negocioData
                    ]);

                    // 6.1. Detalle Inventario
                    if (!empty($negocioData['detalleInventario'])) {
                        foreach ($negocioData['detalleInventario'] as $item) {
                            DetalleInventarioNegocio::create([
                                'id_Datos_Negocio' => $datosNegocio->id,
                                ...$item
                            ]);
                        }
                    }
                }

                // 7. Guardar Garantías
                if (!empty($validatedData['garantias'])) {
                    foreach ($validatedData['garantias'] as $garantiaData) {
                        
                        if (array_key_exists('fecha_ultima_valuacion', $garantiaData) && empty($garantiaData['fecha_ultima_valuacion'])) {
                            $garantiaData['fecha_ultima_valuacion'] = null;
                        }

                        Garantia::create([
                            'id_Evaluacion' => $evaluacion->id,
                            ...$garantiaData
                        ]);
                    }
                }

                // 8. Guardar Firmas (Solo físico)
                
                // Firma Cliente
                // Intentamos buscar en el request con el nombre exacto del FormData
                // Nota: A veces en arrays anidados el request->file se accede con notación punto 'usuario.firmaCliente'
                $firmaCliente = $request->file('usuario.firmaCliente') ?? $request->file('firmaCliente');

                if ($firmaCliente) {
                    $this->fileStorage->storeFile(
                        $firmaCliente, 
                        $usuario->id, 
                        $evaluacion->id, 
                        'firma-cliente', // Subfolder (debe coincidir con ShowEvaluacionAction)
                        'firma_cliente'  // Prefix
                    );
                }

                // Firma Aval
                $firmaAval = $request->file('aval.firmaAval') ?? $request->file('firmaAval');

                if ($firmaAval) {
                    $this->fileStorage->storeFile(
                        $firmaAval, 
                        $usuario->id, 
                        $evaluacion->id, 
                        'firma-aval',   // Subfolder (debe coincidir con ShowEvaluacionAction)
                        'firma_aval'    // Prefix
                    );
                }

                return $usuario->id;
            });

            return ['success' => true, 'message' => 'Evaluación completa registrada exitosamente.', 'usuario_id' => $result];

        } catch (Throwable $e) {
            Log::error('Error en StoreEvaluacionAction: ' . $e->getMessage());
            Log::error($e->getTraceAsString()); 
            return ['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()];
        }
    }
}