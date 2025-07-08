<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AsistenciaFotoController extends Controller
{
    public function store(Request $request, $asistenciaId)
    {
        $request->validate([
            'foto' => 'required|image|max:2048' // Máximo 2MB
        ]);

        $asistencia = Asistencia::findOrFail($asistenciaId);
        
        // Guardar la imagen
        $path = $request->file('foto')->store('public/asistencias');
        $asistencia->foto_registro = Storage::url($path);
        $asistencia->save();

        return response()->json([
            'message' => 'Foto subida correctamente',
            'path' => $asistencia->foto_registro
        ]);
    }
}