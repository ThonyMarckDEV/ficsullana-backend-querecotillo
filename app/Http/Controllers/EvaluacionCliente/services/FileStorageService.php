<?php

namespace App\Http\Controllers\EvaluacionCliente\services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class FileStorageService
{
    public function storeFile(
        UploadedFile $file,
        int $idCliente,
        int $idEvaluacion,
        string $subFolder,  // firma-cliente, firma-aval, activo-fijo, etc.
        string $prefix      // firma_cliente, foto_negocio, etc.
    ): string {

        $disk = 'public';
        $extension = $file->getClientOriginalExtension();

        // Carpeta final
        $path = "clientes/{$idCliente}/evaluaciones/{$idEvaluacion}/{$subFolder}";

        Log::info("Guardando archivo en carpeta: {$path}");

        /* ======================================================
         * 1. BORRAR ARCHIVOS ANTERIORES EN LA CARPETA
         * ====================================================== */
        $previousFiles = Storage::disk($disk)->files($path);

        foreach ($previousFiles as $oldFile) {
            Storage::disk($disk)->delete($oldFile);
            Log::info("Archivo anterior eliminado: {$oldFile}");
        }

        /* ======================================================
         * 2. GUARDAR NUEVO ARCHIVO CON TIMESTAMP
         * ====================================================== */
        $timestamp = now()->format('Y-m-d_His');
        $fileName  = "{$prefix}_{$timestamp}.{$extension}";

        $storedPath = $file->storeAs($path, $fileName, $disk);

        Log::info("Archivo nuevo guardado: {$storedPath}");

        return $storedPath;
    }

    public function deleteFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
            Log::info("Archivo eliminado manualmente: {$path}");
        }
    }

    /**
     * Busca el archivo en la carpeta física y devuelve la URL pública.
     */
    public function getFileUrl(int $idCliente, int $idEvaluacion, string $subFolder): ?string
    {
        $disk = 'public';
        // Reconstruimos la ruta igual que al guardar
        $path = "clientes/{$idCliente}/evaluaciones/{$idEvaluacion}/{$subFolder}";

        // Escaneamos la carpeta buscando archivos
        // (Como tu lógica borra los anteriores, solo debería haber 1 o ninguno)
        $files = Storage::disk($disk)->files($path);

        if (count($files) > 0) {
            // Agarramos el primer archivo que encontremos (ej: firma_cliente_2025...png)
            // y generamos la URL pública
            return Storage::url($files[0]);
        }

        return null;
    }
}
