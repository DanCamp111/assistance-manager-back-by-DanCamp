<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario;

class AdminUsuarioSeeder extends Seeder
{
    public function run()
    {
        Usuario::create([
            'nombre' => 'Admin',
            'apellido_paterno' => 'Sistema',
            'apellido_materno' => 'Principal',
            'correo' => 'admin@example.com',
            'password' => bcrypt('password'),
            'rol_id' => 1, // ID del rol admin
            'status' => 'activo'
        ]);
    }
}