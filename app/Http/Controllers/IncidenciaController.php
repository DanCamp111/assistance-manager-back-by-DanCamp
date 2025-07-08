<?php

namespace App\Http\Controllers;

use App\Models\Incidencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class IncidenciaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = Incidencia::with(['usuario', 'supervisor'])
            ->orderBy('fecha_solicitud', 'desc');
        
        // Filtros básicos
        if ($request->has('usuario_id')) {
            // Solo admin puede filtrar por otros usuarios
            if ($user->rol_id == 1) { 
                $query->where('usuario_id', $request->usuario_id);
            }
        } else {
            // Usuario normal solo ve sus propias incidencias
            if ($user->rol_id != 1) {
                $query->where('usuario_id', $user->id);
            }
        }
        
        // Filtros adicionales
        if ($request->has('estatus')) {
            $query->where('estatus', $request->estatus);
        }
        
        if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
            $query->whereBetween('fecha_ausencia', [$request->fecha_inicio, $request->fecha_fin]);
        }
        
        if ($request->has('tipo_incidencia')) {
            $query->where('tipo_incidencia', $request->tipo_incidencia);
        }
        
        return response()->json($query->paginate($request->get('per_page', 15)));
    }

    public function store(Request $request)
    {
        $user = $request->user();
        
        $validator = Validator::make($request->all(), [
            'tipo_incidencia' => 'required|string|max:50',
            'motivo' => 'required|string',
            'fecha_ausencia' => 'required|date',
            'hora_salida' => 'nullable|date_format:H:i:s',
            'hora_regreso' => 'nullable|date_format:H:i:s|after:hora_salida',
            'hora_transporte' => 'nullable|date_format:H:i:s',
            'supervisor_id' => 'nullable|exists:usuarios,id'
        ]);

        // Asignar automáticamente el usuario logueado
        $validator->after(function ($validator) use ($user) {
            $data = $validator->getData();
            if (isset($data['usuario_id']) && $data['usuario_id'] != $user->id && $user->rol_id != 1) {
                $validator->errors()->add('usuario_id', 'No puedes crear incidencias para otros usuarios');
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();
        $data['usuario_id'] = $user->id;
        $data['estatus'] = 'pendiente';
        $data['fecha_solicitud'] = now();

        $incidencia = Incidencia::create($data);
        
        return response()->json($incidencia, 201);
    }

    public function show($id)
    {
        $incidencia = Incidencia::with(['usuario', 'supervisor'])->findOrFail($id);
        return response()->json($incidencia);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $incidencia = Incidencia::findOrFail($id);
        
        // Validar permisos
        if ($incidencia->usuario_id != $user->id && $user->rol_id != 1) {
            return response()->json([
                'message' => 'No autorizado'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'tipo_incidencia' => 'sometimes|string|max:50',
            'motivo' => 'sometimes|string',
            'fecha_ausencia' => 'sometimes|date',
            'hora_salida' => 'nullable|date_format:H:i:s',
            'hora_regreso' => 'nullable|date_format:H:i:s|after:hora_salida',
            'hora_transporte' => 'nullable|date_format:H:i:s',
            'documento_justificativo' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $incidencia->update($validator->validated());
        return response()->json($incidencia);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $incidencia = Incidencia::findOrFail($id);
        
        // Solo admin o dueño puede eliminar
        if ($incidencia->usuario_id != $user->id && $user->rol_id != 1) {
            return response()->json([
                'message' => 'No autorizado'
            ], 403);
        }

        $incidencia->delete();
        return response()->json(null, 204);
    }

    public function cambiarEstatus(Request $request, $id)
    {
        $user = $request->user();
        $incidencia = Incidencia::findOrFail($id);
        
        // Solo admin o supervisor puede cambiar estatus
        if ($user->rol_id != 1 && $user->id != $incidencia->supervisor_id) {
            return response()->json([
                'message' => 'No autorizado'
            ], 403);
        }

        $request->validate([
            'estatus' => ['required', Rule::in(['aprobado', 'rechazado'])],
            'observaciones' => 'required_if:estatus,rechazado|string|nullable',
            'supervisor_id' => 'required|exists:usuarios,id'
        ]);

        $incidencia->update([
            'estatus' => $request->estatus,
            'observaciones' => $request->observaciones,
            'supervisor_id' => $request->supervisor_id,
            'fecha_revision' => now()
        ]);

        return response()->json($incidencia);
    }
}