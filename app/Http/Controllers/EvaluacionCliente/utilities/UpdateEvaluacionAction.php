<?php

namespace App\Http\Controllers\EvaluacionCliente\utilities;

use App\Http\Controllers\EvaluacionCliente\services\AvalService;
use App\Http\Controllers\EvaluacionCliente\services\ClienteDataService;
use App\Http\Controllers\EvaluacionCliente\services\FileStorageService;
use App\Models\EvaluacionCliente;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class UpdateEvaluacionAction
{
    public function __construct(
        protected ClienteDataService $clienteService,
        protected AvalService $avalService,
        protected FileStorageService $fileStorage
    ) {}

    public function handle(array $data, int $evaluacionId): array
    {
        try {
            DB::transaction(function () use ($data, $evaluacionId) {

                Log::info("=== UPDATE EVALUACION {$evaluacionId} ===");

                $evaluacion = EvaluacionCliente::findOrFail($evaluacionId);
                $clienteId  = $data['usuario']['id'];

                Log::info("Cliente ID: {$clienteId}");

                /* ======================================================
                * 1. PROCESAMIENTO DE ARCHIVOS
                * ===================================================== */

                /* ------------------------------
                 * FIRMA CLIENTE
                 * ------------------------------ */
                if (!empty($data['usuario']['firmaCliente']) &&
                    $data['usuario']['firmaCliente'] instanceof UploadedFile) {

                    $ruta = $this->fileStorage->storeFile(
                        $data['usuario']['firmaCliente'],
                        $clienteId,
                        $evaluacionId,
                        'firma-cliente',      // SUBCARPETA
                        'firma_cliente'       // PREFIX archivo
                    );

                    $data['usuario']['firmaCliente_ruta'] = $ruta;
                }

                unset($data['usuario']['firmaCliente']);

                /* ------------------------------
                 * FOTOS DEL NEGOCIO
                 * ------------------------------ */
                if (isset($data['datosNegocio'])) {

                    $config = [
                        'foto_apuntes_cobranza' => 'fotos-cobranza',
                        'foto_activo_fijo'      => 'activo-fijo',
                        'foto_negocio'          => 'negocio'
                    ];

                    foreach ($config as $campo => $subfolder) {

                        if (!empty($data['datosNegocio'][$campo]) &&
                            $data['datosNegocio'][$campo] instanceof UploadedFile) {

                            $ruta = $this->fileStorage->storeFile(
                                $data['datosNegocio'][$campo],
                                $clienteId,
                                $evaluacionId,
                                $subfolder,               // SUBCARPETA
                                $campo                    // PREFIX
                            );

                            $data['datosNegocio'][$campo] = $ruta;
                        }
                    }
                }

                /* ------------------------------
                 * FIRMA DEL AVAL
                 * ------------------------------ */
                if (!empty($data['aval']['firmaAval']) &&
                    $data['aval']['firmaAval'] instanceof UploadedFile) {

                    $ruta = $this->fileStorage->storeFile(
                        $data['aval']['firmaAval'],
                        $clienteId,
                        $evaluacionId,
                        'firma-aval',
                        'firma_aval'
                    );

                    $data['aval']['firmaAval_ruta'] = $ruta;
                }

                unset($data['aval']['firmaAval']);      // Borra el binario
                unset($data['aval']['firmaAval_ruta']); // Borra la ruta (si no quieres que vaya a la BD)

                /* ======================================================
                 * 2. ACTUALIZAR BD
                 * ===================================================== */

                // CLIENTE
                $usuario = $this->clienteService->createOrUpdate($data['usuario']);

                // AVAL
                $this->avalService->manage($usuario, $data['aval'] ?? null);

                // EVALUACIÓN
                $camposEvaluacion = $data['credito'];
                $camposEvaluacion['estado'] = 0;
                $camposEvaluacion['observaciones'] = null;

                $evaluacion->update($camposEvaluacion);

                // DATOS DEL NEGOCIO
                if (isset($data['datosNegocio'])) {

                    $datosNegocio = $evaluacion->datosNegocio()->updateOrCreate(
                        ['id_Evaluacion' => $evaluacion->id],
                        $data['datosNegocio']
                    );

                    if (isset($data['datosNegocio']['detalleInventario'])) {

                        $datosNegocio->detalleInventario()->delete();

                        if (!empty($data['datosNegocio']['detalleInventario'])) {
                            $datosNegocio->detalleInventario()->createMany(
                                $data['datosNegocio']['detalleInventario']
                            );
                        }
                    }
                }

                // UNIDAD FAMILIAR
                if (isset($data['unidadFamiliar'])) {
                    $evaluacion->unidadFamiliar()->updateOrCreate(
                        ['id_Evaluacion' => $evaluacion->id],
                        $data['unidadFamiliar']
                    );
                }

                // GARANTÍAS
                if (isset($data['garantias'])) {
                    $evaluacion->garantias()->delete();

                    if (!empty($data['garantias'])) {
                        $evaluacion->garantias()->createMany($data['garantias']);
                    }
                }
            });

            return ['success' => true, 'message' => 'Guardado exitoso.'];

        } catch (Throwable $e) {
            Log::error("Error UpdateEvaluacionAction: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
}
