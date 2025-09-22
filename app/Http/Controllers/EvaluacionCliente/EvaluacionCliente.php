<?php

namespace App\Http\Controllers\EvaluacionCliente;

use App\Http\Controllers\EvaluacionCliente\utilities\EvaluacionClienteUtils;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class EvaluacionCliente extends Controller
{
    public function store(Request $request)
    {
        // Obtnemos data y Decodificamos el JSON recibido 
       $data = json_decode($request->input('data'), true);

        $validator = EvaluacionClienteUtils::StoreValidate($data);

        if ($validator->fails()) {
            return response()->json([
                'msg'   => 'Errores de validación',
                'errors'=> $validator->errors()
            ], 422);
        }

        // Accedemos a cada bloque
        $usuario   = $data['usuario'] ?? [];
        $credito   = $data['credito'] ?? [];

        // Ejemplo variables
        $nombreUsuario = $usuario['nombres'] ?? null;
        $montoPrestamo = $credito['montoPrestamo'] ?? null;

        Log::info('Procesando solicitud de préstamo para el usuario: ' . $nombreUsuario);
        Log::info('Monto del préstamo: ' . $montoPrestamo);


        return response()->json([
            'msg'     => 'Datos procesados correctamente',
        ]);
    }

}
