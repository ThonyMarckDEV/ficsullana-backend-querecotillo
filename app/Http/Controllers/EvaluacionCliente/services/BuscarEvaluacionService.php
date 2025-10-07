<?php

namespace App\Http\Controllers\EvaluacionCliente\services;

use App\Models\EvaluacionCliente;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class BuscarEvaluacionService
{
    /**
     * Busca evaluaciones por DNI, aplicando filtros según el rol del usuario.
     */
    public function findByDni(string $dni, User $user): Collection
    {
        $isJefeNegocios = $user->id_Rol == 7;

        // Query base con las relaciones necesarias
        $query = EvaluacionCliente::with('cliente.datos', 'asesor.datos')
            ->whereHas('cliente.datos', function ($q) use ($dni) {
                $q->where('dni', $dni);
            })
            ->latest();

        // Si el usuario NO es jefe de negocios, debe ser un asesor.
        // Filtramos para que solo vea las evaluaciones que él ha creado.
        if (!$isJefeNegocios) {
            $query->where('id_Asesor', $user->id);
        }

        return $query->get();
    }
}