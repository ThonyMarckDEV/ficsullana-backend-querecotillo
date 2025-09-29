<?php

namespace App\Http\Controllers\EvaluacionCliente;

use App\Http\Controllers\EvaluacionCliente\services\ProcesarEvaluacion;
use App\Http\Controllers\EvaluacionCliente\utilities\EvaluacionClienteUtils;
use App\Models\EvaluacionCliente as EvaluacionClienteModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
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
                'msg'    => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        // 2. Llamamos al servicio para procesar y guardar los datos
        $resultado = ProcesarEvaluacion::execute($data);

        // 3. Devolvemos una respuesta basada en el resultado del servicio
        if ($resultado['success']) {
            return response()->json([
                'msg'        => $resultado['message'],
                'usuario_id' => $resultado['usuario_id']
            ], 201); // 201 Created es un buen código de estado para una creación exitosa
        } else {
            return response()->json([
                'msg'    => $resultado['message'],
                'errors' => 'Error en el servidor al procesar la solicitud.'
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Muestra un listado de las evaluaciones asignadas al asesor autenticado.
     */
    public function index()
    {
        try {
            // Obtenemos el ID del usuario (asesor) logeado
            $idAsesorLogeado = Auth::user()->id;

            $evaluaciones = EvaluacionClienteModel::with('cliente.datos')
                // <-- 2. Filtra donde 'id_Asesor' coincida con el del usuario logeado
                ->where('id_Asesor', $idAsesorLogeado) 
                ->latest() // Ordena por las más recientes primero
                ->get();

            return response()->json($evaluaciones);

        } catch (\Exception $e) {
            Log::error('Error al obtener evaluaciones: ' . $e->getMessage());
            return response()->json(['msg' => 'Error al obtener los datos'], 500);
        }
    }


}
