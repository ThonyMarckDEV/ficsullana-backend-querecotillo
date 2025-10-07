<?php

namespace App\Services\EvaluacionCliente;

use App\Models\User;

class AvalService
{
    /**
     * Gestiona el aval de un usuario: lo crea, actualiza o elimina.
     *
     * @param User $usuario El usuario al que pertenece el aval.
     * @param array|null $avalData Los datos del aval, o null para eliminarlo.
     */
    public function manage(User $usuario, ?array $avalData): void
    {
        if ($avalData && !empty($avalData['dniAval'])) {
            $usuario->avales()->updateOrCreate(['id_Cliente' => $usuario->id], $avalData);
        } else {
            $usuario->avales()->delete();
        }
    }
}