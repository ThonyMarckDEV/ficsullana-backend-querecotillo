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
    public function findByDni(?string $dni, ?string $fechaInicio, ?string $fechaFin, User $user): Collection
    {
     
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

        // 2. FILTRO POR DNI
        if (!empty($dni)) {
            $query->whereHas('cliente.datos', function ($q) use ($dni) {
                $q->where('dni', 'like', "%{$dni}%");
            });
        }

        // 3. NUEVO: FILTRO POR RANGO DE FECHAS
        if (!empty($fechaInicio)) {
            // startOfDay asegura que tome desde las 00:00:00
            $query->whereDate('created_at', '>=', $fechaInicio);
        }

        if (!empty($fechaFin)) {
            // endOfDay no es necesario si usas whereDate, pero asegura consistencia
            $query->whereDate('created_at', '<=', $fechaFin);
        }

        // 4. SEGURIDAD POR ROL (Tu lógica existente)
        switch ($user->id_Rol) {
            case 3: $query->where('id_Cliente', $user->id); break;
            case 4: $query->where('id_Asesor', $user->id); break;
        }

        return $query->get();
    }
}