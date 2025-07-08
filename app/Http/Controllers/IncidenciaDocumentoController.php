<?php

namespace App\Http\Controllers;

use App\Models\Incidencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class IncidenciaDocumentoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function store(Request $request, $incidenciaId)
    {
        $user = $request->user();
        $incidencia = Incidencia::findOrFail($incidenciaId);
        
        // Validar permisos
        if ($incidencia->usuario_id != $user->id && $user->rol_id != 1) {
            return response()->json([
                'message' => 'No autorizado'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'documento' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120' // 5MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        // Eliminar documento anterior si existe
        if ($incidencia->documento_justificativo) {
            Storage::delete($incidencia->documento_justificativo);
        }
        
        // Guardar el documento
        $path = $request->file('documento')->store('incidencias/documentos', 'public');
        $incidencia->documento_justificativo = Storage::url($path);
        $incidencia->save();

        return response()->json([
            'message' => 'Documento subido correctamente',
            'path' => $incidencia->documento_justificativo
        ]);
    }
}