<?php

namespace App\Http\Controllers\EvaluacionCliente\services;

use App\Models\ClienteAval;
use App\Models\User;

class AvalService
{
    /**
     * Gestiona el aval y retorna el modelo creado/actualizado.
     */
    public function manage(User $cliente, ?array $data): ?ClienteAval
    {
        if (empty($data) || empty($data['dniAval'])) {
            return null;
        }

        return ClienteAval::updateOrCreate(
            [
                'id_Cliente' => $cliente->id,
                'dniAval'    => $data['dniAval']
            ],
            [
                'apellidoPaternoAval'     => $data['apellidoPaternoAval'] ?? null,
                'apellidoMaternoAval'     => $data['apellidoMaternoAval'] ?? null,
                'nombresAval'             => $data['nombresAval'] ?? null,
                'telefonoFijoAval'        => $data['telefonoFijoAval'] ?? null,
                'telefonoMovilAval'       => $data['telefonoMovilAval'] ?? null,
                'direccionAval'           => $data['direccionAval'] ?? null,
                'referenciaDomicilioAval' => $data['referenciaDomicilioAval'] ?? null,
                'departamentoAval'        => $data['departamentoAval'] ?? null,
                'provinciaAval'           => $data['provinciaAval'] ?? null,
                'distritoAval'            => $data['distritoAval'] ?? null,
                'relacionClienteAval'     => $data['relacionClienteAval'] ?? null,
            ]
        );
    }
}