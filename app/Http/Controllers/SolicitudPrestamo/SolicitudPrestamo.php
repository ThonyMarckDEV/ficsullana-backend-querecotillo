<?php

namespace App\Http\Controllers\SolicitudPrestamo;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class SolicitudPrestamo extends Controller
{
    public function store(Request $request)
    {
        // Obtenemos "data" (string JSON)
        $data = $request->input('data');

        // Lo convertimos en array
        $data = json_decode($data, true);

        // Accedemos a cada bloque
        $usuario   = $data['usuario'] ?? [];
        $credito   = $data['credito'] ?? [];

        // Ejemplo variables
        $nombreUsuario = $usuario['nombres'] ?? null;
        $montoPrestamo = $credito['montoPrestamo'] ?? null;

        Log::info('Procesando solicitud de préstamo para el usuario: ' . $nombreUsuario);
        Log::info('Monto del préstamo: ' . $montoPrestamo);


        // Si quieres también manejar el PDF
        // if ($request->hasFile('pdf')) {
        //     $pdfFile = $request->file('pdf');
        //     $pdfPath = $pdfFile->store('solicitudes'); // lo guarda en storage/app/solicitudes
        // }

        return response()->json([
            'msg'     => 'Datos procesados correctamente',
            'usuario' => $nombreUsuario,
            'monto' => $montoPrestamo,
        ]);
    }

}
