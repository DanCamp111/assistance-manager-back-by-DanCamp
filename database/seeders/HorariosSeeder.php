<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HorariosSeeder extends Seeder
{
    public function run(): void
    {
        $dias = ['lunes', 'martes', 'miércoles', 'jueves', 'viernes'];

        foreach ($dias as $dia) {
            DB::table('horarios')->updateOrInsert(
                [
                    'usuario_id' => null,
                    'dia_semana' => $dia
                ],
                [
                    'hora_entrada' => '09:00:00',
                    'hora_salida' => '18:00:00',
                    'hora_comida_inicio' => '14:00:00',
                    'hora_comida_fin' => '15:00:00',
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }
    }
}
