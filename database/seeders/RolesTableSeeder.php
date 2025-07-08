<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rol;

class RolesTableSeeder extends Seeder
{
    public function run()
    {
        Rol::create([
            'nombre' => 'SuperAdmin',
            'descripcion' => 'Administrador del sistema'
        ]);

        Rol::create([
            'nombre' => 'supervisor',
            'descripcion' => 'Supervisor de personal'
        ]);

        Rol::create([
            'nombre' => 'usuario',
            'descripcion' => 'Usuario regular'
        ]);
    }
}