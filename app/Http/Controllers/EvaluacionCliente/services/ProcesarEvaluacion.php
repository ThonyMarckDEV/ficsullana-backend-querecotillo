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
    public static function execute(array $data): array
    {
        $usuarioData = $data['usuario'];
        $creditoData = $data['credito'];
        $avalData    = $data['aval'] ?? null;

        try {
            $resultado = DB::transaction(function () use ($usuarioData, $creditoData, $avalData) {
                
                $datos = null;
                $usuario = null;

                // --- LÓGICA CORREGIDA PARA MANEJAR CLIENTES NUEVOS Y EXISTENTES ---
                
                // Verificamos si el cliente ya existe (si el frontend nos envió un 'id')
                if (isset($usuarioData['id']) && $usuarioData['id']) {
                    
                    // --- CASO: CLIENTE EXISTENTE ---
                    Log::info("Cliente existente encontrado. ID de Datos: {$usuarioData['id']}");
                    
                    // 1. Buscamos sus datos y su usuario
                    $datos = Datos::findOrFail($usuarioData['id']);
                    $usuario = $datos->usuario;
                    
                    // 2. Actualizamos sus datos por si el asesor hizo algún cambio
                    // El método updateOrCreate buscará por el primer array y actualizará con el segundo.
                    // Esto es seguro tanto para clientes nuevos como existentes.
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
                    Direccion::create(array_merge($usuarioData, ['id_Datos' => $datos->id]));
                    Contacto::create(array_merge($usuarioData, ['id_Datos' => $datos->id]));
                    CuentaBancaria::create(array_merge($usuarioData, ['id_Datos' => $datos->id]));
                    ClienteEmpleo::create(array_merge($usuarioData, ['id_Datos' => $datos->id]));
                }

                // --- LÓGICA COMÚN PARA AMBOS CASOS ---

                // 4. Procesar el Aval
                if ($avalData && !empty($avalData['dniAval'])) {
                    // Busca un aval para este cliente, si existe lo actualiza, si no, lo crea.
                    $usuario->avales()->updateOrCreate(['id_Cliente' => $usuario->id], $avalData);
                } else {
                    // Si no se envía un aval, nos aseguramos que no haya ninguno antiguo
                    $usuario->avales()->delete();
                }

                // 5. SIEMPRE se crea una NUEVA evaluación
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
                ]);

                return $usuario->id;
            });
            
            Log::info("Evaluación procesada exitosamente. ID de Usuario: {$resultado}");
            return ['success' => true, 'message' => 'Datos procesados y guardados correctamente.', 'usuario_id' => $resultado];

        } catch (Throwable $e) {
            Log::error("Error al procesar la evaluación del cliente: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return ['success' => false, 'message' => 'Ocurrió un error interno al guardar los datos.'];
        }
    }
}