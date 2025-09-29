<?php

namespace App\Http\Controllers\EvaluacionCliente;

use App\Http\Controllers\EvaluacionCliente\services\ProcesarEvaluacion;
use App\Http\Controllers\EvaluacionCliente\utilities\EvaluacionClienteUtils;
use App\Models\Datos;
use App\Models\EvaluacionCliente as EvaluacionClienteModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
     * Busca evaluaciones por DNI de cliente, asignadas al asesor autenticado.
     */
    public function index(Request $request) // <-- Acepta el Request
    {
        $request->validate([
            'dni' => 'required|string|digits_between:8,9',
        ]);

        try {
            $dni = $request->input('dni');
            $idAsesorLogeado = Auth::id();

            // Usamos 'whereHas' para buscar dentro de las relaciones
            $evaluaciones = EvaluacionClienteModel::with('cliente.datos')
                ->where('id_Asesor', $idAsesorLogeado)
                ->whereHas('cliente.datos', function ($query) use ($dni) {
                    // Aquí filtramos por el DNI en la tabla 'datos'
                    $query->where('dni', $dni);
                })
                ->latest()
                ->get();

            return response()->json($evaluaciones);

        } catch (\Exception $e) {
            Log::error('Error al obtener evaluaciones por DNI: ' . $e->getMessage());
            return response()->json(['msg' => 'Error al buscar los datos'], 500);
        }
    }

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
                'cuentasBancarias' // Asegúrate que la relación se llame así en el modelo Datos
            ])->where('dni', $dni)->firstOrFail();

            // Buscamos la última evaluación rechazada para ese cliente
            $evaluacion = EvaluacionClienteModel::whereHas('cliente.datos', function ($query) use ($dni) {
                $query->where('dni', $dni);
            })->where('estado', 2)->latest()->first();

            return response()->json([
                'usuario' => $datos,
                'evaluacion' => $evaluacion,
                'aval' => $datos->usuario->avales->first() ?? null // Devuelve el primer aval si existe
            ]);

        } catch (Throwable $e) {
            Log::error('Error al obtener datos para corrección: ' . $e->getMessage());
            return response()->json(['msg' => 'No se encontraron datos para el DNI proporcionado.'], 404);
        }
    }

    /**
     * Actualiza una evaluación y los datos del cliente.
     */
    public function update(Request $request, $evaluacionId)
    {
        $data = $request->all();
        $usuarioData = $data['usuario'];
        $creditoData = $data['credito'];
        $avalData = $data['aval'] ?? null;

        DB::beginTransaction();
        try {
            // 1. Encuentra y actualiza los datos principales
            $datos = Datos::findOrFail($usuarioData['id']);
            $datos->update($usuarioData);

            // 2. Actualiza los modelos relacionados (contacto, dirección, etc.)
            // (Asumiendo una relación de uno a uno para simplificar)
            $datos->contactos()->first()->update($usuarioData);
            $datos->direcciones()->first()->update($usuarioData);
            $datos->empleos()->first()->update($usuarioData);
            $datos->cuentasBancarias()->first()->update($usuarioData);

            // 3. Actualiza o crea el Aval
            $usuario = User::find($datos->usuario->id);
            if ($avalData) {
                $usuario->avales()->updateOrCreate(
                    ['id_Cliente' => $usuario->id], // Busca por id_Cliente
                    $avalData // Actualiza o crea con esta data
                );
            } else {
                // Si no se envía aval, se elimina el existente
                $usuario->avales()->delete();
            }
            
            // 4. Actualiza la evaluación: cambia estado a PENDIENTE y actualiza datos
            $evaluacion = EvaluacionClienteModel::findOrFail($evaluacionId);
            $evaluacion->update([
                'producto' => $creditoData['producto'],
                'monto_prestamo' => $creditoData['montoPrestamo'],
                'tasa_interes' => $creditoData['tasaInteres'],
                'cuotas' => $creditoData['cuotas'],
                'modalidad_credito' => $creditoData['modalidad'],
                'destino_credito' => $creditoData['destinoCredito'],
                'periodo_credito' => $creditoData['periodoCredito'],
                'estado' => 0, // Vuelve a PENDIENTE
                'observaciones' => null, // Limpia las observaciones de rechazo
            ]);

            DB::commit();
            return response()->json(['msg' => 'Evaluación corregida y enviada exitosamente.']);

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Error al actualizar evaluación: ' . $e->getMessage());
            return response()->json(['msg' => 'Error al guardar los cambios.'], 500);
        }
    }


}
