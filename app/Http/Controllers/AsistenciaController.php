<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\Horario;
use App\Models\Incidencia;
use Carbon\Carbon;
use Carbon\CarbonPeriod;


class AsistenciaController extends Controller
{
    public function __construct()
    {
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

        $asistencias = $query->paginate($request->get('per_page', 15));

        return response()->json($asistencias);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'usuario_id' => 'required|exists:usuarios,id',
            'tipo_registro' => ['required', Rule::in(['entrada', 'salida'])],
            'fecha_registro' => 'required|date',
            'hora_exacta' => 'required|date_format:H:i:s',
            'foto_registro' => 'nullable|string'
        ]);

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

        if ($asistencia->usuario_id != $user->id && !$user->esAdmin()) {
            return response()->json(['message' => 'No autorizado'], 403);
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

        if ($asistencia->usuario_id != $user->id && !$user->esAdmin()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $asistencia->delete();
        return response()->json(null, 204);
    }

    public function resumenSemanal(Request $request)
    {
        $user = $request->user();
        $usuarioId = $user->id;

        // Configurar Carbon en español
        Carbon::setLocale('es');
        $inicioSemana = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $finSemana = Carbon::now()->endOfWeek(Carbon::FRIDAY); // Solo hasta viernes

        // Mapeo de días en español (para compatibilidad con la base de datos)
        $diasSemana = [
            'monday' => 'lunes',
            'tuesday' => 'martes',
            'wednesday' => 'miércoles',
            'thursday' => 'jueves',
            'friday' => 'viernes'
        ];

        // Obtener asistencias
        $asistencias = Asistencia::where('usuario_id', $usuarioId)
            ->whereBetween('fecha_registro', [$inicioSemana, $finSemana])
            ->orderBy('fecha_registro')
            ->orderBy('hora_exacta')
            ->get()
            ->groupBy(function ($item) {
                return Carbon::parse($item->fecha_registro)->toDateString();
            });

        // Obtener incidencias
        $incidencias = Incidencia::where('usuario_id', $usuarioId)
            ->whereBetween('fecha_ausencia', [$inicioSemana, $finSemana])
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->fecha_ausencia)->toDateString();
            });

        // Obtener todos los horarios laborables (de lunes a viernes)
        $horarios = Horario::where(function ($query) use ($usuarioId) {
            $query->where('usuario_id', $usuarioId)
                ->orWhereNull('usuario_id');
        })
            ->whereIn('dia_semana', array_values($diasSemana))
            ->get()
            ->keyBy('dia_semana');

        $dias = [];
        $periodo = CarbonPeriod::create($inicioSemana, $finSemana);

        foreach ($periodo as $fecha) {
            $fechaStr = $fecha->toDateString();
            $diaSemanaIngles = strtolower($fecha->englishDayOfWeek);
            $diaSemanaEspanol = $diasSemana[$diaSemanaIngles] ?? $diaSemanaIngles;

            // Obtener el horario aplicable (buscar por nombre en español)
            $horario = $horarios->get($diaSemanaEspanol);

            // Obtener asistencia del día
            $registrosDia = $asistencias[$fechaStr] ?? collect();

            $entrada = $registrosDia->firstWhere('tipo_registro', 'entrada');
            $salida = $registrosDia->firstWhere('tipo_registro', 'salida');

            // Formatear horas
            $horaEntrada = $entrada ? Carbon::parse($entrada->hora_exacta)->format('H:i:s') : null;
            $horaSalida = $salida ? Carbon::parse($salida->hora_exacta)->format('H:i:s') : null;

            // Calcular retardo
            $retardo = 'No';
            if ($horaEntrada && $horario) {
                $horaEntradaCarbon = Carbon::parse($horaEntrada);
                $horaEsperadaCarbon = Carbon::parse($horario->hora_entrada);

                if ($horaEntradaCarbon->greaterThan($horaEsperadaCarbon)) {
                    $retardo = $horaEntradaCarbon->diffInMinutes($horaEsperadaCarbon) . ' min';
                }
            }

            // Formatear horas de comida (mostrar siempre para días laborables)
            $horasComida = $horario ?
                Carbon::parse($horario->hora_comida_inicio)->format('H:i') . ' - ' .
                Carbon::parse($horario->hora_comida_fin)->format('H:i') : '-';

            // Incidencia del día
            $incidencia = $incidencias[$fechaStr] ?? null;

            $dias[] = [
                'dia' => ucfirst($fecha->dayName), // Nombre localizado
                'fecha' => $fechaStr,
                'entrada' => $horaEntrada ?? '-',
                'salida' => $horaSalida ?? '-',
                'horas_comida' => $horasComida,
                'retardo' => $retardo,
                'incidencias' => $incidencia ? $incidencia->tipo_incidencia : 'Ninguna',
                'observaciones' => $incidencia ? $incidencia->observaciones : '-',
            ];
        }

        return response()->json($dias);
    }

    public function resumenSemanalAdmin(Request $request)
    {
        // Verificar que el usuario sea administrador (rol 1)
        $user = $request->user();
        if (!$user->esAdmin()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        // Configurar Carbon en español
        Carbon::setLocale('es');
        $inicioSemana = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $finSemana = Carbon::now()->endOfWeek(Carbon::FRIDAY);

        // Mapeo de días en español
        $diasSemana = [
            'monday' => 'lunes',
            'tuesday' => 'martes',
            'wednesday' => 'miércoles',
            'thursday' => 'jueves',
            'friday' => 'viernes'
        ];

        // Obtener todos los usuarios activos con sus horarios
        $usuarios = Usuario::with('horarios')
            ->where('status', 'activo')
            ->get();

        // Obtener todas las asistencias de la semana agrupadas por usuario y fecha
        $asistencias = Asistencia::whereBetween('fecha_registro', [$inicioSemana, $finSemana])
            ->get()
            ->groupBy(['usuario_id', function ($item) {
                return Carbon::parse($item->fecha_registro)->toDateString();
            }]);

        // Obtener todas las incidencias de la semana agrupadas por usuario y fecha
        $incidencias = Incidencia::whereBetween('fecha_ausencia', [$inicioSemana, $finSemana])
            ->get()
            ->groupBy(['usuario_id', function ($item) {
                return Carbon::parse($item->fecha_ausencia)->toDateString();
            }]);

        // Obtener todos los horarios agrupados por usuario y día
        $horarios = Horario::whereIn('dia_semana', array_values($diasSemana))
            ->get()
            ->groupBy(['usuario_id', 'dia_semana']);

        $resultado = [];

        foreach ($usuarios as $usuario) {
            $periodo = CarbonPeriod::create($inicioSemana, $finSemana);

            foreach ($periodo as $fecha) {
                $fechaStr = $fecha->toDateString();
                $diaSemanaIngles = strtolower($fecha->englishDayOfWeek);
                $diaSemanaEspanol = $diasSemana[$diaSemanaIngles] ?? $diaSemanaIngles;

                // Obtener horario para este usuario y día
                $horario = $horarios
                    ->get($usuario->id, collect())
                    ->get($diaSemanaEspanol)
                    ?? $horarios->get(null, collect())->get($diaSemanaEspanol);

                // Si hay más de un horario para ese día, tomamos el primero
                 $horario = $horario instanceof \Illuminate\Support\Collection ? $horario->first() : $horario;


                // Obtener registros de asistencia para este usuario y fecha
                $registrosDia = $asistencias
                    ->get($usuario->id, collect())
                    ->get($fechaStr, collect());

                $entrada = $registrosDia->firstWhere('tipo_registro', 'entrada');
                $salida = $registrosDia->firstWhere('tipo_registro', 'salida');

                // Formatear horas
                $horaEntrada = $entrada ? Carbon::parse($entrada->hora_exacta)->format('H:i') : null;
                $horaSalida = $salida ? Carbon::parse($salida->hora_exacta)->format('H:i') : null;

                // Calcular retardo
                $retardo = 'No';
                if ($horaEntrada && $horario) {
                    $horaEntradaCarbon = Carbon::parse($horaEntrada);
                    $horaEsperadaCarbon = Carbon::parse(optional($horario->first())->hora_entrada);

                    if ($horaEntradaCarbon->greaterThan($horaEsperadaCarbon)) {
                        $retardo = $horaEntradaCarbon->diffInMinutes($horaEsperadaCarbon) . ' min';
                    }
                }

                // Obtener incidencia para este usuario y fecha
                $incidencia = $incidencias
                    ->get($usuario->id, collect())
                    ->get($fechaStr, collect())
                    ->first();

                $resultado[] = [
                    'usuario_id' => $usuario->id,
                    'nombre' => $usuario->nombre_completo,
                    'fecha' => $fechaStr,
                    'dia' => ucfirst($fecha->dayName),
                    'hora_entrada' => $horaEntrada ?? '-',
                    'hora_salida' => $horaSalida ?? '-',
                    'retardo' => $retardo,
                    'incidencia' => $incidencia ? $incidencia->tipo_incidencia : 'Ninguna',
                    'observaciones' => $incidencia ? $incidencia->observaciones : '-',
                    'horario_entrada' => $horario ? Carbon::parse($horario->hora_entrada)->format('H:i') : '-',
                    'horario_salida' => $horario ? Carbon::parse($horario->hora_salida)->format('H:i') : '-',
                ];
            }
        }

        // Ordenar por fecha y nombre
        $resultado = collect($resultado)->sortBy([
            ['fecha', 'asc'],
            ['nombre', 'asc']
        ])->values()->all();

        return response()->json($resultado);
    }
}
