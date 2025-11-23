<?php

namespace App\Http\Controllers\EvaluacionCliente\services;

use App\Models\EvaluacionCliente;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class BuscarEvaluacionService
{
    /**
     * Busca evaluaciones. Si hay DNI, filtra por él. Si no, devuelve todas (según rol).
     * Carga relaciones profundas para el CreditScore y la Ficha de Cliente.
     *
     * @param string|null $dni El DNI del cliente (puede ser null).
     * @param User $user El usuario autenticado.
     * @return Collection
     */
    public function findByDni(?string $dni, User $user): Collection
    {
        // 1. Query base con relaciones profundas
        $query = EvaluacionCliente::with([
            // Datos completos del Cliente para Score y Modal
            'cliente.datos',
            'cliente.datos.contactos',
            'cliente.datos.direcciones',
            'cliente.datos.empleos',
            'cliente.datos.cuentasBancarias',
            
            // Datos de la Evaluación
            'asesor.datos',
            'datosNegocio.detalleInventario',
            'unidadFamiliar',
            'garantias',
            'aval'
        ])->latest(); 

        // 2. FILTRO DINÁMICO
        if (!empty($dni)) {
            $query->whereHas('cliente.datos', function ($q) use ($dni) {
                $q->where('dni', 'like', "%{$dni}%");
            });
        }

        // 3. SEGURIDAD POR ROL
        switch ($user->id_Rol) {
            case 3: // Cliente
                $query->where('id_Cliente', $user->id);
                break;
            case 4: // Asesor
                $query->where('id_Asesor', $user->id);
                break;
            case 7: // Jefe
                break;
        }

        return $query->get();
    }
}