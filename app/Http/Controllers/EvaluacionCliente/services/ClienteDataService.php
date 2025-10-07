<?php

namespace App\Http\Controllers\EvaluacionCliente\services;

use App\Models\Datos;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ClienteDataService
{
    /**
     * Crea o actualiza un cliente y todos sus datos relacionados.
     *
     * @param array $data Los datos del 'usuario' validados.
     * @return User El modelo de usuario creado o actualizado.
     */
    public function createOrUpdate(array $data): User
    {
        // Si se provee un ID, es una actualización. Si no, es una creación.
        if (isset($data['id']) && $data['id']) {
            $datos = Datos::findOrFail($data['id']);
            $datos->update($data);
            $usuario = $datos->usuario;
        } else {
            $datos = Datos::create($data);
            $usuario = User::create([
                'username' => $data['dni'],
                'password' => Hash::make($data['dni']),
                'id_Datos' => $datos->id,
            ]);
        }

        // Actualiza o crea las relaciones en cascada
        $datos->contactos()->updateOrCreate(['id_Datos' => $datos->id], $data);
        $datos->direcciones()->updateOrCreate(['id_Datos' => $datos->id], $data);
        $datos->empleos()->updateOrCreate(['id_Datos' => $datos->id], $data);
        $datos->cuentasBancarias()->updateOrCreate(['id_Datos' => $datos->id], $data);

        return $usuario;
    }
}