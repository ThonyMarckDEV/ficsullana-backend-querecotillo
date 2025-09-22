<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Insertamos el registro en tabla datos
        $idDato = DB::table('datos')->insertGetId([
            'nombre' => 'Anthony',
            'apellidoPaterno' => 'Mendoza',
            'apellidoMaterno' => 'Sanchez',
            'apellidoConyuge' => null,
            'estadoCivil' => 'Soltero',
            'sexo' => 'Masculino',
            'dni' => '61883939',
            'fechaCaducidadDni' => '2029-09-14',
            'ruc' => null,
            'expuesta' => 0,
            'aval' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Insertamos el usuario con idDato relacionado
        DB::table('usuarios')->insert([
            'username' => 'thonymarck',
            'password' => Hash::make('123456'),
            'id_Datos' => $idDato,
            'id_Rol' => 4, // Rol asesor
            'estado' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
