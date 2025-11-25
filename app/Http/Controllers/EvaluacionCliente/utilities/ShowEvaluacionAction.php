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
        // Asumiendo que la carpeta padre es el ID del cliente
        $idCliente = $evaluacion->cliente->id; 
        $idEvaluacion = $evaluacion->id;

        // 3. BUSCAR FIRMA CLIENTE
        // Carpeta física: 'firma-cliente'
        $urlCliente = $this->fileStorage->getFileUrl($idCliente, $idEvaluacion, 'firma-cliente');
        
        if ($evaluacion->cliente && $evaluacion->cliente->datos) {
            $evaluacion->cliente->datos->url_firma = $urlCliente;
        }

        // 4. BUSCAR FIRMA AVAL
        // Carpeta física: 'firma-aval'
        $urlAval = $this->fileStorage->getFileUrl($idCliente, $idEvaluacion, 'firma-aval');
        
        if ($evaluacion->aval) {
            $evaluacion->aval->url_firma = $urlAval;
        }

        // 5. (OPCIONAL) FOTOS NEGOCIO
        /*
        if ($evaluacion->datosNegocio) {
            // Ejemplo para foto del negocio
            $evaluacion->datosNegocio->url_foto_negocio = 
                $this->fileStorage->getFileUrl($idCliente, $idEvaluacion, 'negocio');
                
            // Ejemplo para foto cobranza
            $evaluacion->datosNegocio->url_foto_cobranza = 
                $this->fileStorage->getFileUrl($idCliente, $idEvaluacion, 'fotos-cobranza');
        }
        */

        return $evaluacion;
    }
}