<?php

namespace App\Http\Controllers\EvaluacionCliente\services;

use App\Models\EvaluacionCliente;
use App\Models\ClienteAval;
use App\Models\ClienteEmpleo;
use App\Models\Contacto;
use App\Models\CuentaBancaria;
use App\Models\Datos;
use App\Models\Direccion;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcesarEvaluacion
{
    /**
     * Procesa la evaluación del cliente, incluyendo la creación o actualización de datos
     * y la validación de evaluaciones pendientes/rechazadas.
     *
     * @param array $data
     * @return array
     */
    public static function execute(array $data): array
    {
        $usuarioData = $data['usuario'];
        $creditoData = $data['credito'];
        $avalData    = $data['aval'] ?? null;

        // --- 1. VALIDACIÓN PREVIA DE EVALUACIONES EXISTENTES ---

        // Solo validamos si es un cliente existente (si nos envía el ID)
        if (isset($usuarioData['id']) && $usuarioData['id']) {
            try {
                // Buscamos el usuario asociado a los 'Datos' para validar sus evaluaciones
                $datos = Datos::findOrFail($usuarioData['id']);
                $usuario = $datos->usuario;
                
                if ($usuario) {
                    $evaluacionExistente = EvaluacionCliente::where('id_Cliente', $usuario->id)
                        ->whereIn('estado', [0, 2]) // 0: Pendiente, 2: Rechazado
                        ->first();

                    if ($evaluacionExistente) {
                        $mensaje = $evaluacionExistente->estado == 0 
                            ? 'El cliente tiene una evaluación **pendiente** de revisión. No puedes enviar otra.' 
                            : 'El cliente tiene una evaluación **rechazada**. Por favor, revisa su situación antes de volver a aplicar.';

                        Log::warning("Cliente con ID de Usuario {$usuario->id} intentó aplicar con evaluación en estado: {$evaluacionExistente->estado}.");
                        
                        return [
                            'success' => false, 
                            'message' => $mensaje, 
                            'status_code' => 409 // Conflict
                        ];
                    }
                }
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                // Si no encuentra los Datos, se asumirá que es un cliente nuevo dentro del transaction.
                // O se lanzará un error si el ID estaba mal (lo manejamos en el catch principal).
            }
        }
        
        // --- 2. PROCESAMIENTO DE LA EVALUACIÓN (DENTRO DE LA TRANSACCIÓN) ---

        try {
            $resultado = DB::transaction(function () use ($usuarioData, $creditoData, $avalData) {
                
                $datos = null;
                $usuario = null;

                // --- LÓGICA PARA MANEJAR CLIENTES NUEVOS Y EXISTENTES ---
                
                if (isset($usuarioData['id']) && $usuarioData['id']) {
                    
                    // --- CASO: CLIENTE EXISTENTE ---
                    Log::info("Cliente existente encontrado. ID de Datos: {$usuarioData['id']}");
                    
                    // 1. Buscamos sus datos y su usuario
                    // Si falla, ModelNotFoundException será atrapada por el catch principal
                    $datos = Datos::findOrFail($usuarioData['id']);
                    $usuario = $datos->usuario;
                    
                    // 2. Actualizamos sus datos (siempre usando updateOrCreate en las relaciones)
                    $datos->contactos()->updateOrCreate(['id_Datos' => $datos->id], $usuarioData);
                    $datos->direcciones()->updateOrCreate(['id_Datos' => $datos->id], $usuarioData);
                    $datos->empleos()->updateOrCreate(['id_Datos' => $datos->id], $usuarioData);
                    $datos->cuentasBancarias()->updateOrCreate(['id_Datos' => $datos->id], $usuarioData);

                } else {
                    
                    // --- CASO: CLIENTE NUEVO ---
                    Log::info("Registrando cliente nuevo con DNI: {$usuarioData['dni']}");

                    // 1. Crear el registro en la tabla 'datos'
                    $datos = Datos::create($usuarioData);

                    // 2. Crear el 'usuario'
                    $usuario = User::create([
                        'username' => $usuarioData['dni'],
                        'password' => Hash::make($usuarioData['dni']),
                        'id_Datos' => $datos->id,
                    ]);

                    // 3. Crear sus datos relacionados
                    // Se usa array_merge para incluir 'id_Datos' en los datos a guardar
                    Direccion::create(array_merge($usuarioData, ['id_Datos' => $datos->id]));
                    Contacto::create(array_merge($usuarioData, ['id_Datos' => $datos->id]));
                    CuentaBancaria::create(array_merge($usuarioData, ['id_Datos' => $datos->id]));
                    ClienteEmpleo::create(array_merge($usuarioData, ['id_Datos' => $datos->id]));
                }

                // 4. Procesar el Aval
                if ($avalData && !empty($avalData['dniAval'])) {
                    // Busca un aval asociado a este cliente ('$usuario'), si existe lo actualiza, si no, lo crea.
                    // Asumiendo que la relación 'avales()' en el modelo User es a ClienteAval.
                    $usuario->avales()->updateOrCreate(['id_Cliente' => $usuario->id], $avalData);
                } else {
                    // Si no se envía un aval, elimina cualquier aval anterior asociado.
                    $usuario->avales()->delete();
                }

                // 5. SIEMPRE se crea una NUEVA evaluación (Inicialmente en estado 0: Pendiente)
                EvaluacionCliente::create([
                    'id_Asesor'         => Auth::id(),
                    'id_Cliente'        => $usuario->id,
                    'producto'          => $creditoData['producto'],
                    'montoPrestamo'     => $creditoData['montoPrestamo'],
                    'tasaInteres'       => $creditoData['tasaInteres'],
                    'cuotas'            => $creditoData['cuotas'],
                    'modalidadCredito'  => $creditoData['modalidadCredito'],
                    'destinoCredito'    => $creditoData['destinoCredito'],
                    'periodoCredito'    => $creditoData['periodoCredito'],
                    'estado'            => 0, // Aseguramos que el estado inicial sea Pendiente
                ]);

                return $usuario->id;
            });
            
            Log::info("Evaluación procesada exitosamente. ID de Usuario: {$resultado}");
            return ['success' => true, 'message' => 'Datos procesados y guardados correctamente.', 'usuario_id' => $resultado];

        } catch (Throwable $e) {
            // Manejo de errores de la base de datos o lógica de la aplicación
            Log::error("Error al procesar la evaluación del cliente: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'data' => $data
            ]);
            
            // Mensaje más genérico para el usuario
            $errorMessage = 'Ocurrió un error interno al guardar los datos.';
            
            // Si el error es una ModelNotFoundException, se puede dar un mensaje más específico.
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                 $errorMessage = 'El cliente existente con el ID proporcionado no fue encontrado.';
            }

            return ['success' => false, 'message' => $errorMessage];
        }
    }
}