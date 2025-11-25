<?php

namespace App\Http\Controllers\EvaluacionCliente\utilities;

use App\Http\Controllers\EvaluacionCliente\services\AvalService;
use App\Http\Controllers\EvaluacionCliente\services\ClienteDataService;
use App\Models\EvaluacionCliente;
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
                // 1. Actualizar Cliente (Usuario)
                // Se usa 'usuario' del payload validado
                $usuario = $this->clienteService->createOrUpdate($data['usuario']);

                // 2. Gestionar Aval
                // Se usa 'aval' del payload validado (puede ser null)
                $this->avalService->manage($usuario, $data['aval'] ?? null);
                
                // 3. Obtener la Evaluación
                $evaluacion = EvaluacionCliente::findOrFail($evaluacionId);

                // 4. Actualizar Datos Principales de la Evaluación
                // Solo campos propios de la tabla 'evaluaciones'
                $evaluacion->update([
                    ...$data['credito'], // montoPrestamo, cuotas, tasaInteres, etc.
                    'estado'        => 0,    // Volver a PENDIENTE
                    'observaciones' => null, // Limpiar observaciones
                ]);

                // 5. Actualizar DATOS DEL NEGOCIO (Si vienen en el request)
                $datosNegocio = null;
                if (isset($data['datosNegocio']) && is_array($data['datosNegocio'])) {
                    // updateOrCreate busca por id_Evaluacion y actualiza los campos
                    $datosNegocio = $evaluacion->datosNegocio()->updateOrCreate(
                        ['id_Evaluacion' => $evaluacion->id],
                        $data['datosNegocio'] // Laravel filtra automáticamente las columnas que coinciden
                    );

                    // 5.1 Actualizar INVENTARIO
                    if (isset($data['datosNegocio']['detalleInventario'])) {
                        // Estrategia limpia: Borrar anteriores y crear nuevos para evitar lógica compleja de IDs
                        // Nota: Si hay IDs en el array, Laravel los ignorará en createMany, pero para updates parciales considera sync
                        $datosNegocio->detalleInventario()->delete();
                        if (!empty($data['datosNegocio']['detalleInventario'])) {
                            // Asegurar que los campos como unidad_medida, precios, etc., se mapeen correctamente
                            // Si el frontend envía camelCase, mapear a snake_case aquí si es necesario (pero en el JSON es snake_case)
                            $inventarioItems = collect($data['datosNegocio']['detalleInventario'])->map(function ($item) use ($datosNegocio) {
                                return [
                                    'id_Datos_Negocio' => $datosNegocio->id,
                                    'nombre_producto' => $item['nombre_producto'] ?? null,
                                    'unidad_medida' => $item['unidad_medida'] ?? null,
                                    'precio_compra_unitario' => $item['precio_compra_unitario'] ?? 0,
                                    'precio_venta_unitario' => $item['precio_venta_unitario'] ?? 0,
                                    'margen_ganancia' => $item['margen_ganancia'] ?? null,
                                    'cantidad_inventario' => $item['cantidad_inventario'] ?? 0,
                                    'precio_total_estimado' => $item['precio_total_estimado'] ?? 0,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ];
                            })->toArray();

                            $datosNegocio->detalleInventario()->createMany($inventarioItems);
                        }
                    }
                }

                // 6. Actualizar UNIDAD FAMILIAR
                if (isset($data['unidadFamiliar']) && is_array($data['unidadFamiliar'])) {
                    $evaluacion->unidadFamiliar()->updateOrCreate(
                        ['id_Evaluacion' => $evaluacion->id],
                        $data['unidadFamiliar']
                    );
                }

                // 7. Actualizar GARANTÍAS
                if (isset($data['garantias']) && is_array($data['garantias'])) {
                    // Borramos las garantías anteriores y recreamos
                    // (Ojo: Si manejas fotos en S3/Storage, aquí deberías tener cuidado de no borrar archivos físicos)
                    $evaluacion->garantias()->delete();
                    if (!empty($data['garantias'])) {
                        // Mapear para asegurar campos requeridos y timestamps
                        $garantiaItems = collect($data['garantias'])->map(function ($item) use ($evaluacion) {
                            return [
                                'id_Evaluacion' => $evaluacion->id,
                                'es_declaracion_jurada' => $item['es_declaracion_jurada'] ?? 0,
                                'moneda' => $item['moneda'] ?? 'PEN',
                                'clase_garantia' => $item['clase_garantia'] ?? null,
                                'documento_garantia' => $item['documento_garantia'] ?? null,
                                'tipo_garantia' => $item['tipo_garantia'] ?? null,
                                'descripcion_bien' => $item['descripcion_bien'] ?? null,
                                'direccion_bien' => $item['direccion_bien'] ?? null,
                                'monto_garantia' => $item['monto_garantia'] ?? 0,
                                'valor_comercial' => $item['valor_comercial'] ?? 0,
                                'valor_realizacion' => $item['valor_realizacion'] ?? 0,
                                'ficha_registral' => $item['ficha_registral'] ?? null,
                                'fecha_ultima_valuacion' => $item['fecha_ultima_valuacion'] ?? null,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        })->toArray();

                        $evaluacion->garantias()->createMany($garantiaItems);
                    }
                }
            });

            return ['success' => true, 'message' => 'Evaluación corregida y enviada exitosamente.'];
        } catch (Throwable $e) {
            Log::error("Error en UpdateEvaluacionAction para evaluacion ID {$evaluacionId}: " . $e->getMessage());
            // Para debug, puedes descomentar la siguiente línea temporalmente:
            // return ['success' => false, 'message' => $e->getMessage()]; 
            return ['success' => false, 'message' => 'Error al guardar los cambios en la base de datos.'];
        }
    }
}