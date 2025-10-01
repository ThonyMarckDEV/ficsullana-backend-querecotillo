<?php

namespace App\Http\Controllers\ClienteController;

use App\Models\Datos;
use App\Http\Controllers\Controller;
use App\Models\EvaluacionCliente as EvaluacionClienteModel;
use Illuminate\Support\Facades\Log;
use Throwable;

class ClienteController extends Controller
{

         /**
     * Obtiene todos los datos asociados a un cliente por su DNI para la corrección.
     */
    public function show($dni)
    {
        try {
             // Esta consulta ahora podrá seguir la cadena de relaciones sin problemas
            $datos = Datos::with([
                'usuario.avales', // Carga el usuario y, a través de él, sus avales
                'contactos', 
                'direcciones', 
                'empleos', 
                'cuentasBancarias'
            ])->where('dni', $dni)->firstOrFail();

            // Buscamos la última evaluación rechazada para ese cliente
            $evaluacion = EvaluacionClienteModel::whereHas('cliente.datos', function ($query) use ($dni) {
                $query->where('dni', $dni);
            })->where('estado', 2)->latest()->first();

            return response()->json([
                'datosCliente' => $datos,
                'evaluacion' => $evaluacion,
                'aval' => $datos->usuario?->avales->first() ?? null
            ]);

        } catch (Throwable $e) {
            Log::error('Error al obtener datos para corrección: ' . $e->getMessage());
            return response()->json(['msg' => 'No se encontraron datos para el DNI proporcionado.'], 404);
        }
    }

}