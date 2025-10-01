<?php

namespace App\Http\Controllers\EvaluacionCliente;

use App\Http\Controllers\EvaluacionCliente\services\ProcesarEvaluacion;
use App\Http\Controllers\EvaluacionCliente\utilities\EvaluacionClienteValidations;
use App\Models\Datos;
use App\Models\EvaluacionCliente as EvaluacionClienteModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class EvaluacionClienteController extends Controller
{
    public function store(Request $request)
    {
        // Obtnemos data y Decodificamos el JSON recibido 
        $data = json_decode($request->input('data'), true);

        $validator = EvaluacionClienteValidations::StoreValidate($data);

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
     * Si el usuario es jefe_negocios, obtiene todas las evaluaciones del cliente sin filtrar por asesor.
     */
    public function index(Request $request) // <-- Acepta el Request
    {
        $request->validate([
            'dni' => 'required|string|digits_between:8,9',
        ]);

        try {
            $dni = $request->input('dni');
            $user = Auth::user();
            $idAsesorLogeado = $user->id;
            $isJefeNegocios = $user->id_Rol == 7;
            $isAsesor = $user->id_Rol == 4;

            // Verificar rol requerido
            if (!$isAsesor && !$isJefeNegocios) {
                return response()->json(['msg' => 'Acceso denegado. Se requiere rol: asesor o jefe de negocios'], 403);
            }

            // Query base
            $query = EvaluacionClienteModel::with('cliente.datos')
                ->whereHas('cliente.datos', function ($q) use ($dni) {
                    $q->where('dni', $dni);
                })
                ->latest();

            // Si no es jefe_negocios, filtra por id_Asesor (solo aplica para asesores)
            if (!$isJefeNegocios) {
                $query->where('id_Asesor', $idAsesorLogeado);
            }

            $evaluaciones = $query->get();

            return response()->json($evaluaciones);

        } catch (\Exception $e) {
            Log::error('Error al obtener evaluaciones por DNI: ' . $e->getMessage());
            return response()->json(['msg' => 'Error al buscar los datos'], 500);
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
                'montoPrestamo' => $creditoData['montoPrestamo'],
                'tasaInteres' => $creditoData['tasaInteres'],
                'cuotas' => $creditoData['cuotas'],
                'modalidadCredito' => $creditoData['modalidadCredito'],
                'destinoCredito' => $creditoData['destinoCredito'],
                'periodoCredito' => $creditoData['periodoCredito'],
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

    /**
     * Actualiza el estado de una evaluación (solo para jefe_negocios: aprobar o rechazar).
     */
    public function updateStatus(Request $request, $evaluacionId)
    {
        $request->validate([
            'estado' => 'required|in:1,2',
            'observaciones' => 'nullable|string|required_if:estado,2|max:500'
        ]);

        try {
            $user = Auth::user();
            if ($user->id_Rol != 7) {
                return response()->json(['msg' => 'Acceso denegado. Solo jefe de negocios puede actualizar estados.'], 403);
            }

            $evaluacion = EvaluacionClienteModel::findOrFail($evaluacionId);

            if ($evaluacion->estado != 0) {
                return response()->json(['msg' => 'Solo las evaluaciones pendientes pueden ser aprobadas o rechazadas.'], 400);
            }

            $updateData = [
                'estado' => $request->estado,
            ];

            if ($request->estado == 2) {
                $updateData['observaciones'] = $request->observaciones;
            } else {
                $updateData['observaciones'] = null;
            }

            $evaluacion->update($updateData);

            return response()->json(['msg' => 'Estado de la evaluación actualizado exitosamente.']);

        } catch (\Exception $e) {
            Log::error('Error al actualizar estado de evaluación: ' . $e->getMessage());
            return response()->json(['msg' => 'Error al actualizar el estado.'], 500);
        }
    }
}