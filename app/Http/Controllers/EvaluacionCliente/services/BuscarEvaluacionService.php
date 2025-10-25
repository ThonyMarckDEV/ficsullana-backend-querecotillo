<?php

namespace App\Http\Controllers\EvaluacionCliente\services;

use App\Models\EvaluacionCliente;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class BuscarEvaluacionService
{
    /**
     * Busca evaluaciones por DNI, aplicando filtros según el rol del usuario.
     *
     * @param string $dni El DNI del cliente a buscar.
     * @param User $user El usuario autenticado que realiza la búsqueda.
     * @return Collection
     */
    public function findByDni(string $dni, User $user): Collection
    {
        // Query base para encontrar evaluaciones por el DNI del cliente,
        // cargando las relaciones necesarias.
        $query = EvaluacionCliente::with('cliente.datos', 'asesor.datos')
            ->whereHas('cliente.datos', function ($q) use ($dni) {
                $q->where('dni', $dni);
            })
            ->latest();

        // Aplicar filtros adicionales basados en el rol del usuario.
        switch ($user->id_Rol) {
            // Caso 1: El usuario es un Cliente.
            // Solo puede ver sus propias evaluaciones.
            case 3:
                $query->where('id_Cliente', $user->id);
                break;

            // Caso 2: El usuario es un Asesor.
            // Solo puede ver las evaluaciones que él ha creado.
            case 4:
                $query->where('id_Asesor', $user->id);
                break;
                
            // Caso 3: El usuario es Jefe de Negocios (o cualquier otro rol superior).
            // Puede ver todas las evaluaciones del cliente buscado.
            case 7:
                // No se aplican filtros adicionales.
                break;
        }

        // Ejecutar la consulta y devolver los resultados.
        return $query->get();
    }
}