<?php

namespace App\Http\Controllers;

use App\Models\ReporteGenerado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ReporteGeneradoController extends Controller
{
    public function index(Request $request)
    {
        $query = ReporteGenerado::with('supervisor');
        
        // Filtros
        if ($request->has('supervisor_id')) {
            $query->where('supervisor_id', $request->supervisor_id);
        }
        
        if ($request->has('tipo_reporte')) {
            $query->where('tipo_reporte', $request->tipo_reporte);
        }
        
        if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
            $query->whereBetween('fecha_creacion', [$request->fecha_inicio, $request->fecha_fin]);
        }
        
        $reportes = $query->orderBy('created_at', 'desc')->get();
        return response()->json($reportes);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'supervisor_id' => 'required|exists:usuarios,id',
            'tipo_reporte' => ['required', Rule::in(['asistencias', 'incidencias', 'combinado'])],
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'formato' => ['required', Rule::in(['pdf', 'excel'])],
            'nombre_archivo' => 'required|string|max:255',
            'ruta_archivo' => 'required|string|max:255',
            'parametros' => 'nullable|json'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $reporte = ReporteGenerado::create($request->all());
        return response()->json($reporte, 201);
    }

    public function show($id)
    {
        $reporte = ReporteGenerado::with('supervisor')->findOrFail($id);
        return response()->json($reporte);
    }

    public function destroy($id)
    {
        $reporte = ReporteGenerado::findOrFail($id);
        
        // Aquí podrías agregar lógica para eliminar el archivo físico
        // Storage::delete($reporte->ruta_archivo);
        
        $reporte->delete();
        return response()->json(null, 204);
    }

    public function generarReporte(Request $request)
    {
        $request->validate([
            'supervisor_id' => 'required|exists:usuarios,id',
            'tipo_reporte' => ['required', Rule::in(['asistencias', 'incidencias', 'combinado'])],
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'formato' => ['required', Rule::in(['pdf', 'excel'])]
        ]);

        // Generar nombre único para el archivo
        $nombreArchivo = 'reporte_'.strtolower($request->tipo_reporte).'_'.now()->format('YmdHis').'.'.$request->formato;
        $rutaArchivo = 'storage/reportes/'.$nombreArchivo;
        
        // Parámetros para el reporte
        $parametros = [
            'filtros' => [
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
                'formato' => $request->formato
            ],
            'opciones' => [
                'incluir_logo' => true,
                'orientacion' => 'portrait'
            ]
        ];

        // Crear registro del reporte (la generación real del archivo sería en un Job)
        $reporte = ReporteGenerado::create([
            'supervisor_id' => $request->supervisor_id,
            'tipo_reporte' => $request->tipo_reporte,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'formato' => $request->formato,
            'nombre_archivo' => $nombreArchivo,
            'ruta_archivo' => $rutaArchivo,
            'parametros' => json_encode($parametros)
        ]);

        // Disparar job para generar el reporte (lo implementarás después)
        // GenerarReporteJob::dispatch($reporte);

        return response()->json([
            'message' => 'Reporte en proceso de generación',
            'reporte' => $reporte
        ], 202);
    }
}