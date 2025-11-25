<?php

namespace App\Http\Controllers\EvaluacionCliente\utilities;

use App\Http\Controllers\EvaluacionCliente\services\FileStorageService;
use App\Models\EvaluacionCliente;

class ShowEvaluacionAction
{
    public function __construct(
        protected FileStorageService $fileStorage
    ) {}

    /**
     * Busca la evaluación y adjunta las URLs de las firmas si existen.
     * Retorna null si no encuentra la evaluación.
     */
    public function handle(int $id): ?EvaluacionCliente
    {
        // 1. CARGA DE DATOS Y RELACIONES
        $evaluacion = EvaluacionCliente::with([
            'cliente.datos.contactos',
            'cliente.datos.direcciones',
            'cliente.datos.empleos',
            'cliente.datos.cuentasBancarias',            
            'aval',
            'unidadFamiliar',
            'datosNegocio.detalleInventario',
            'garantias'
        ])->find($id);

        if (!$evaluacion) {
            return null;
        }

        // 2. OBTENER IDs PARA BUSCAR ARCHIVOS
        $idCliente = $evaluacion->cliente->id; 
        $idEvaluacion = $evaluacion->id;

        // 3. BUSCAR FIRMA CLIENTE
        $urlCliente = $this->fileStorage->getFileUrl($idCliente, $idEvaluacion, 'firma-cliente');
        
        if ($evaluacion->cliente && $evaluacion->cliente->datos) {
            $evaluacion->cliente->datos->url_firma = $urlCliente;
        }

        // 4. BUSCAR FIRMA AVAL
        $urlAval = $this->fileStorage->getFileUrl($idCliente, $idEvaluacion, 'firma-aval');
        
        if ($evaluacion->aval) {
            $evaluacion->aval->url_firma = $urlAval;
        }

        // 5. BUSCAR FOTOS DEL NEGOCIO (Cobranza y Activo Fijo)
        if ($evaluacion->datosNegocio) {
            // Foto Apuntes de Cobranza
            $evaluacion->datosNegocio->url_foto_cobranza = 
                $this->fileStorage->getFileUrl($idCliente, $idEvaluacion, 'fotos-cobranza');

            // Foto Activo Fijo
            $evaluacion->datosNegocio->url_foto_activo_fijo = 
                $this->fileStorage->getFileUrl($idCliente, $idEvaluacion, 'activo-fijo');
                
            // (Opcional) Foto del Negocio General
            $evaluacion->datosNegocio->url_foto_negocio = 
                $this->fileStorage->getFileUrl($idCliente, $idEvaluacion, 'negocio');
        }

        return $evaluacion;
    }
}