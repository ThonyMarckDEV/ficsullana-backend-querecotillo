<?php

namespace App\Http\Controllers\EvaluacionCliente\services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class FileStorageService
{
    /**
     * Guarda un archivo en la ruta: /clientes/{idCliente}/evaluaciones/{idEvaluacion}/{nombreArchivo_timestamp.ext}
     */
    public function storeFile(UploadedFile $file, int $idCliente, int $idEvaluacion, string $prefix): string
    {
        $disk = 'public'; // Usualmente linkeado a storage/app/public
        
        // Generar nombre único: firmaCliente_2023-10-27_153022.png
        $timestamp = Carbon::now()->format('Y-m-d_His');
        $extension = $file->getClientOriginalExtension();
        $fileName = "{$prefix}_{$timestamp}.{$extension}";

        // Definir ruta
        $path = "clientes/{$idCliente}/evaluaciones/{$idEvaluacion}";

        // Guardar archivo
        $storedPath = $file->storeAs($path, $fileName, $disk);

        // Retornar URL relativa o absoluta según necesites (aquí devolvemos path relativo)
        return $storedPath;
    }
}