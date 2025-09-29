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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcesarEvaluacion
{
    /**
     * Procesa y guarda toda la información de la evaluación del cliente.
     * Utiliza una transacción para garantizar la integridad de los datos.
     *
     * @param array $data Los datos validados del request.
     * @return array Un array con el estado del proceso y un mensaje.
     */
    public static function execute(array $data): array
    {
        // Extraemos los bloques de datos principales
        $usuarioData = $data['usuario'];
        $creditoData = $data['credito']; // Aunque no se usa para guardar, lo extraemos por consistencia
        $avalData    = $data['aval'] ?? null; // El aval es opcional

        try {
            // Iniciamos una transacción de base de datos
            $resultado = DB::transaction(function () use ($usuarioData, $creditoData , $avalData) {
                
                // 1. Crear el registro en la tabla 'datos'
                $datos = Datos::create([
                    'nombre' => $usuarioData['nombre'],
                    'apellidoPaterno' => $usuarioData['apellidoPaterno'],
                    'apellidoMaterno' => $usuarioData['apellidoMaterno'],
                    'apellidoConyuge' => $usuarioData['apellidoConyuge'] ?? null,
                    'estadoCivil' => $usuarioData['estadoCivil'],
                    'sexo' => $usuarioData['sexo'],
                    'dni' => $usuarioData['dni'],
                    'fechaCaducidadDni' => $usuarioData['fechaCaducidadDni'],
                    'nacionalidad' => $usuarioData['nacionalidad'],
                    'residePeru' => $usuarioData['residePeru'],
                    'nivelEducativo' => $usuarioData['nivelEducativo'],
                    'profesion' => $usuarioData['profesion'],
                    'enfermedadesPreexistentes' => $usuarioData['enfermedadesPreexistentes'],
                    'ruc' => $usuarioData['ruc'] ?? null,
                    'expuesta' => $usuarioData['expuesta'] ?? false,
                ]);

                // 2. Crear el 'usuario' con la contraseña igual al DNI (hasheada)
                $usuario = User::create([
                    'username' => $usuarioData['dni'],
                    'password' => Hash::make($usuarioData['dni']),
                    'id_Datos' => $datos->id,
                    // id_Rol y estado tienen valores por defecto en la migración
                ]);

                // 3. Crear la 'direccion'
                Direccion::create([
                    'id_Datos' => $datos->id,
                    'direccionFiscal' => $usuarioData['direccionFiscal'],
                    'direccionCorrespondencia' => $usuarioData['direccionCorrespondencia'],
                    'departamento' => $usuarioData['departamento'],
                    'provincia' => $usuarioData['provincia'],
                    'distrito' => $usuarioData['distrito'],
                    'tipoVivienda' => $usuarioData['tipoVivienda'],
                    'tiempoResidencia' => $usuarioData['tiempoResidencia'],
                    'referenciaDomicilio' => $usuarioData['referenciaDomicilio'],
                ]);

                // 4. Crear el 'contacto'
                Contacto::create([
                    'id_Datos' => $datos->id,
                    'tipo' => 'PRINCIPAL', // O según venga en el JSON
                    'telefonoMovil' => $usuarioData['telefonoMovil'],
                    'telefonoFijo' => $usuarioData['telefonoFijo'] ?? null,
                    'correo' => $usuarioData['correo'] ?? null,
                ]);
                
                // 5. Crear la 'cuenta_bancaria'
                CuentaBancaria::create([
                    'id_Datos' => $datos->id,
                    'ctaAhorros' => $usuarioData['ctaAhorros'],
                    'cci' => $usuarioData['cci'] ?? null,
                    'entidadFinanciera' => $usuarioData['entidadFinanciera'],
                ]);

                // 6. Crear el 'empleo'
                ClienteEmpleo::create([
                    'id_Datos' => $datos->id,
                    'centroLaboral' => $usuarioData['centroLaboral'],
                    'ingresoMensual' => $usuarioData['ingresoMensual'],
                    'inicioLaboral' => $usuarioData['inicioLaboral'],
                    'situacionLaboral' => $usuarioData['situacionLaboral'],
                ]);

                // 7. Si existe un aval, crearlo
                if ($avalData) {
                    ClienteAval::create([
                        'id_Cliente' => $usuario->id, // El FK es con la tabla usuarios
                        'dniAval' => $avalData['dniAval'],
                        'apellidoPaternoAval' => $avalData['apellidoPaternoAval'],
                        'apellidoMaternoAval' => $avalData['apellidoMaternoAval'],
                        'nombresAval' => $avalData['nombresAval'],
                        'telefonoFijoAval' => $avalData['telefonoFijoAval'],
                        'telefonoMovilAval' => $avalData['telefonoMovilAval'],
                        'direccionAval' => $avalData['direccionAval'],
                        'referenciaDomicilioAval' => $avalData['referenciaDomicilioAval'],
                        'departamentoAval' => $avalData['departamentoAval'],
                        'provinciaAval' => $avalData['provinciaAval'],
                        'distritoAval' => $avalData['distritoAval'],
                        'relacionClienteAval' => $avalData['relacionClienteAval'],
                    ]);
                }

                
                // 8. Subir la Evaluacion de Cliente (¡LA PARTE NUEVA!)
                EvaluacionCliente::create([
                    'id_Cliente'        => $usuario->id,
                    'producto'          => $creditoData['producto'],
                    'monto_prestamo'    => $creditoData['montoPrestamo'],
                    'tasa_interes'      => $creditoData['tasaInteres'],
                    'cuotas'            => $creditoData['cuotas'],
                    'modalidad_credito' => $creditoData['modalidad'],
                    'destino_credito'   => $creditoData['destinoCredito'],
                    'periodo_credito'   => $creditoData['periodoCredito'],
                    // El estado y observaciones tienen valores por defecto o son nulos
                ]);

                // Si todo fue exitoso, retornamos el ID del nuevo usuario
                return $usuario->id;
            });
            
            Log::info("Cliente y evaluación creados exitosamente. ID de Usuario: {$resultado}");
            return ['success' => true, 'message' => 'Datos procesados y guardados correctamente.', 'usuario_id' => $resultado];

        } catch (Throwable $e) {
            // Si algo falla, la transacción hará un rollback automático.
            Log::error("Error al procesar la evaluación del cliente: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return ['success' => false, 'message' => 'Ocurrió un error interno al guardar los datos.'];
        }
    }
}