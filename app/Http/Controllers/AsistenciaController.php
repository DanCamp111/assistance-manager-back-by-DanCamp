<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AsistenciaController extends Controller
{
    public function __construct()
    {
        // Aplicar middleware de autenticación a todos los métodos excepto index y show si lo deseas
        $this->middleware('auth:sanctum')->except(['index', 'show']);
    }

    public function index(Request $request)
    {
        $query = Asistencia::with('usuario');
        
        // Filtros
        if ($request->has('usuario_id')) {
            $query->where('usuario_id', $request->usuario_id);
        }
        
        if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
            $query->whereBetween('fecha_registro', [$request->fecha_inicio, $request->fecha_fin]);
        }
        
        if ($request->has('tipo_registro')) {
            $query->where('tipo_registro', $request->tipo_registro);
        }
        
        // Paginación (recomendado)
        $asistencias = $query->paginate($request->get('per_page', 15));
        
        return response()->json($asistencias);
    }

    public function store(Request $request)
    {
        // Obtener usuario autenticado
        $user = $request->user();
        
        $validator = Validator::make($request->all(), [
            'usuario_id' => 'required|exists:usuarios,id',
            'tipo_registro' => ['required', Rule::in(['entrada', 'salida'])],
            'fecha_registro' => 'required|date',
            'hora_exacta' => 'required|date_format:H:i:s',
            'foto_registro' => 'nullable|string'
        ]);

        // Validar que el usuario solo pueda registrarse a sí mismo
        $validator->after(function ($validator) use ($request, $user) {
            if ($request->usuario_id != $user->id && !$user->esAdmin()) {
                $validator->errors()->add('usuario_id', 'No tienes permiso para registrar asistencias de otros usuarios');
            }
            
            $exists = Asistencia::where('usuario_id', $request->usuario_id)
                ->where('fecha_registro', $request->fecha_registro)
                ->where('tipo_registro', $request->tipo_registro)
                ->exists();
                
            if ($exists) {
                $validator->errors()->add('registro', 'Ya existe un registro de este tipo para el usuario en la fecha especificada');
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $asistencia = Asistencia::create($request->all());
        return response()->json($asistencia, 201);
    }

    public function show($id)
    {
        $asistencia = Asistencia::with('usuario')->findOrFail($id);
        return response()->json($asistencia);
    }

    public function update(Request $request, $id)
    {
        $asistencia = Asistencia::findOrFail($id);
        $user = $request->user();

        // Validar que solo el dueño o admin pueda actualizar
        if ($asistencia->usuario_id != $user->id && !$user->esAdmin()) {
            return response()->json([
                'message' => 'No autorizado'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'usuario_id' => 'sometimes|exists:usuarios,id',
            'tipo_registro' => ['sometimes', Rule::in(['entrada', 'salida'])],
            'fecha_registro' => 'sometimes|date',
            'hora_exacta' => 'sometimes|date_format:H:i:s',
            'foto_registro' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $asistencia->update($request->all());
        return response()->json($asistencia);
    }

    public function destroy(Request $request, $id)
    {
        $asistencia = Asistencia::findOrFail($id);
        $user = $request->user();

        // Validar que solo el dueño o admin pueda eliminar
        if ($asistencia->usuario_id != $user->id && !$user->esAdmin()) {
            return response()->json([
                'message' => 'No autorizado'
            ], 403);
        }

        $asistencia->delete();
        return response()->json(null, 204);
    }
}