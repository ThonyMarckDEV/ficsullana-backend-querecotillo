<?php

namespace App\Http\Controllers\EvaluacionCliente\services;

use App\Models\Datos;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Arr; // Importa la clase Arr

class ClienteDataService
{
    /**
     * Crea o actualiza un cliente y todos sus datos relacionados buscando por DNI.
     *
     * @param array $data Los datos del 'usuario' validados.
     * @return User El modelo de usuario creado o actualizado.
     */
    public function createOrUpdate(array $data): User
    {
        // 1. Usa updateOrCreate para buscar por DNI y actualizar o crear los datos personales.
        //    Esto soluciona el error de DNI duplicado.
        $datos = Datos::updateOrCreate(
            ['dni' => $data['dni']], // Criterio de búsqueda
            Arr::only($data, $this->getDatosFields()) // Datos para actualizar/crear
        );

        // 2. Usa updateOrCreate para la cuenta de usuario (User).
        //    Esto evita crear usuarios duplicados para el mismo cliente.
        $usuario = User::updateOrCreate(
            ['id_Datos' => $datos->id], // Criterio de búsqueda
            [
                'username' => $data['dni'],
                'password' => Hash::make($data['dni']),
            ]
        );

        // 3. Actualiza o crea las relaciones pasando solo los campos necesarios.
        $datos->contactos()->updateOrCreate(
            ['id_Datos' => $datos->id],
            Arr::only($data, $this->getContactoFields())
        );
        $datos->direcciones()->updateOrCreate(
            ['id_Datos' => $datos->id],
            Arr::only($data, $this->getDireccionFields())
        );
        $datos->empleos()->updateOrCreate(
            ['id_Datos' => $datos->id],
            Arr::only($data, $this->getEmpleoFields())
        );
        $datos->cuentasBancarias()->updateOrCreate(
            ['id_Datos' => $datos->id],
            Arr::only($data, $this->getCuentaBancariaFields())
        );

        return $usuario;
    }

    // --- MÉTODOS AYUDANTES PARA OBTENER LOS CAMPOS DE CADA TABLA ---
    
    private function getDatosFields(): array
    {
        return ['dni', 'apellidoPaterno', 'apellidoMaterno', 'nombre', 'fechaNacimiento', 'fechaCaducidadDni', 'sexo', 'estadoCivil', 'nacionalidad', 'residePeru', 'nivelEducativo', 'profesion', 'enfermedadesPreexistentes', 'expuestaPoliticamente'];
    }

    private function getContactoFields(): array
    {
        return ['telefonoFijo', 'telefonoMovil', 'correo'];
    }

    private function getDireccionFields(): array
    {
        return ['direccionFiscal', 'direccionCorrespondencia', 'departamento', 'provincia', 'distrito', 'tipoV vivienda', 'tiempoResidencia', 'referenciaDomicilio'];
    }

    private function getEmpleoFields(): array
    {
        return ['centroLaboral', 'ingresoMensual', 'inicioLaboral', 'situacionLaboral'];
    }

    private function getCuentaBancariaFields(): array
    {
        return ['ctaAhorros', 'cci', 'entidadFinanciera'];
    }
}