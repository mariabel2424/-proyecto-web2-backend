<?php
namespace App\Http\Controllers\API\Cursos;

use App\Http\Controllers\Controller;
use App\Models\Asistencia;
use App\Models\GrupoCurso;
use App\Models\InscripcionCurso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AsistenciaController extends Controller
{
    /**
     * Listar asistencias de un grupo en una fecha
     */
    public function index(Request $request, $idGrupo)
    {
        $grupo = GrupoCurso::with('curso')->findOrFail($idGrupo);
        
        // Verificar que el instructor tenga acceso al grupo
        $user = Auth::user();
        if ($user->isInstructor() && $grupo->id_instructor !== $user->id_usuario) {
            return response()->json([
                'message' => 'No tienes acceso a este grupo'
            ], 403);
        }

        // Fecha por defecto: hoy en zona horaria de Ecuador
        $fecha = $request->get('fecha', Carbon::now('America/Guayaquil')->toDateString());

        // Obtener deportistas inscritos activos
        $inscripciones = InscripcionCurso::with('deportista')
            ->where('id_grupo', $idGrupo)
            ->where('estado', 'activa')
            ->get();

        // Obtener asistencias del día
        $asistencias = Asistencia::where('id_grupo', $idGrupo)
            ->whereDate('fecha', $fecha)
            ->get()
            ->keyBy('id_deportista');

        // Combinar datos
        $estudiantes = $inscripciones->map(function($inscripcion) use ($asistencias, $fecha) {
            $asistencia = $asistencias->get($inscripcion->id_deportista);
            return [
                'id_inscripcion' => $inscripcion->id_inscripcion,
                'id_deportista' => $inscripcion->id_deportista,
                'deportista' => [
                    'id_deportista' => $inscripcion->deportista->id_deportista,
                    'nombres' => $inscripcion->deportista->nombres,
                    'apellidos' => $inscripcion->deportista->apellidos,
                    'cedula' => $inscripcion->deportista->cedula,
                ],
                'asistencia' => $asistencia ? [
                    'id_asistencia' => $asistencia->id_asistencia,
                    'estado' => $asistencia->estado,
                    'observaciones' => $asistencia->observaciones,
                ] : null,
            ];
        });

        return response()->json([
            'grupo' => [
                'id_grupo' => $grupo->id_grupo,
                'nombre' => $grupo->nombre,
                'curso' => $grupo->curso->nombre,
                'horario' => $grupo->hora_inicio . ' - ' . $grupo->hora_fin,
            ],
            'fecha' => $fecha,
            'fecha_formato' => Carbon::parse($fecha)->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY'),
            'total_estudiantes' => $inscripciones->count(),
            'presentes' => $asistencias->where('estado', 'presente')->count(),
            'ausentes' => $asistencias->where('estado', 'ausente')->count(),
            'estudiantes' => $estudiantes,
        ]);
    }

    /**
     * Registrar asistencia masiva
     */
    public function registrar(Request $request, $idGrupo)
    {
        $request->validate([
            'fecha' => 'required|date',
            'asistencias' => 'required|array',
            'asistencias.*.id_deportista' => 'required|exists:deportistas,id_deportista',
            'asistencias.*.estado' => 'required|in:presente,ausente,tardanza,justificado',
            'asistencias.*.observaciones' => 'nullable|string|max:255',
        ]);

        $grupo = GrupoCurso::findOrFail($idGrupo);
        $user = Auth::user();

        // Verificar que el instructor tenga acceso al grupo
        if ($user->isInstructor() && $grupo->id_instructor !== $user->id_usuario) {
            return response()->json([
                'message' => 'No tienes acceso a este grupo'
            ], 403);
        }

        $fecha = $request->fecha;
        $registrados = 0;
        $actualizados = 0;

        foreach ($request->asistencias as $item) {
            $asistencia = Asistencia::updateOrCreate(
                [
                    'id_grupo' => $idGrupo,
                    'id_deportista' => $item['id_deportista'],
                    'fecha' => $fecha,
                ],
                [
                    'id_instructor' => $user->id_usuario,
                    'estado' => $item['estado'],
                    'observaciones' => $item['observaciones'] ?? null,
                ]
            );

            if ($asistencia->wasRecentlyCreated) {
                $registrados++;
            } else {
                $actualizados++;
            }
        }

        return response()->json([
            'message' => 'Asistencia registrada exitosamente',
            'registrados' => $registrados,
            'actualizados' => $actualizados,
            'fecha' => $fecha,
        ]);
    }

    /**
     * Obtener resumen de asistencias de un grupo
     */
    public function resumen(Request $request, $idGrupo)
    {
        $grupo = GrupoCurso::with('curso')->findOrFail($idGrupo);
        
        $user = Auth::user();
        if ($user->isInstructor() && $grupo->id_instructor !== $user->id_usuario) {
            return response()->json([
                'message' => 'No tienes acceso a este grupo'
            ], 403);
        }

        // Rango de fechas (por defecto último mes)
        $fechaInicio = $request->get('fecha_inicio', Carbon::now('America/Guayaquil')->subMonth()->toDateString());
        $fechaFin = $request->get('fecha_fin', Carbon::now('America/Guayaquil')->toDateString());

        // Obtener todas las asistencias en el rango
        $asistencias = Asistencia::with('deportista')
            ->where('id_grupo', $idGrupo)
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->get();

        // Agrupar por deportista
        $porDeportista = $asistencias->groupBy('id_deportista')->map(function($items, $idDeportista) {
            $deportista = $items->first()->deportista;
            return [
                'id_deportista' => $idDeportista,
                'deportista' => $deportista->nombres . ' ' . $deportista->apellidos,
                'total_clases' => $items->count(),
                'presentes' => $items->where('estado', 'presente')->count(),
                'ausentes' => $items->where('estado', 'ausente')->count(),
                'tardanzas' => $items->where('estado', 'tardanza')->count(),
                'justificados' => $items->where('estado', 'justificado')->count(),
                'porcentaje_asistencia' => $items->count() > 0 
                    ? round(($items->where('estado', 'presente')->count() / $items->count()) * 100, 1)
                    : 0,
            ];
        })->values();

        // Agrupar por fecha
        $porFecha = $asistencias->groupBy(function($item) {
            return $item->fecha->format('Y-m-d');
        })->map(function($items, $fecha) {
            return [
                'fecha' => $fecha,
                'fecha_formato' => Carbon::parse($fecha)->locale('es')->isoFormat('ddd D MMM'),
                'total' => $items->count(),
                'presentes' => $items->where('estado', 'presente')->count(),
                'ausentes' => $items->where('estado', 'ausente')->count(),
            ];
        })->sortKeys()->values();

        return response()->json([
            'grupo' => $grupo->nombre,
            'curso' => $grupo->curso->nombre,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'resumen_general' => [
                'total_registros' => $asistencias->count(),
                'presentes' => $asistencias->where('estado', 'presente')->count(),
                'ausentes' => $asistencias->where('estado', 'ausente')->count(),
                'tardanzas' => $asistencias->where('estado', 'tardanza')->count(),
                'justificados' => $asistencias->where('estado', 'justificado')->count(),
            ],
            'por_deportista' => $porDeportista,
            'por_fecha' => $porFecha,
        ]);
    }

    /**
     * Exportar asistencias de un día en formato para descarga
     */
    public function exportar(Request $request, $idGrupo)
    {
        $grupo = GrupoCurso::with(['curso', 'instructor'])->findOrFail($idGrupo);
        
        $user = Auth::user();
        if ($user->isInstructor() && $grupo->id_instructor !== $user->id_usuario) {
            return response()->json([
                'message' => 'No tienes acceso a este grupo'
            ], 403);
        }

        $fecha = $request->get('fecha', Carbon::now('America/Guayaquil')->toDateString());

        // Obtener deportistas inscritos
        $inscripciones = InscripcionCurso::with('deportista')
            ->where('id_grupo', $idGrupo)
            ->where('estado', 'activa')
            ->get();

        // Obtener asistencias del día
        $asistencias = Asistencia::where('id_grupo', $idGrupo)
            ->whereDate('fecha', $fecha)
            ->get()
            ->keyBy('id_deportista');

        $lista = $inscripciones->map(function($inscripcion, $index) use ($asistencias) {
            $asistencia = $asistencias->get($inscripcion->id_deportista);
            $estadoTexto = match($asistencia?->estado) {
                'presente' => 'PRESENTE',
                'ausente' => 'AUSENTE',
                'tardanza' => 'TARDANZA',
                'justificado' => 'JUSTIFICADO',
                default => 'SIN REGISTRAR',
            };
            return [
                'numero' => $index + 1,
                'cedula' => $inscripcion->deportista->cedula ?? '-',
                'nombres' => $inscripcion->deportista->nombres,
                'apellidos' => $inscripcion->deportista->apellidos,
                'estado' => $estadoTexto,
                'observaciones' => $asistencia?->observaciones ?? '',
            ];
        });

        $fechaFormato = Carbon::parse($fecha)->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY');
        $horaGeneracion = Carbon::now('America/Guayaquil')->format('d/m/Y H:i');

        return response()->json([
            'titulo' => 'REGISTRO DE ASISTENCIA',
            'curso' => $grupo->curso->nombre,
            'grupo' => $grupo->nombre,
            'instructor' => $grupo->instructor ? $grupo->instructor->nombre_completo : 'Sin asignar',
            'horario' => $grupo->hora_inicio . ' - ' . $grupo->hora_fin,
            'dias' => $grupo->dias_semana_nombres,
            'fecha' => $fechaFormato,
            'hora_generacion' => $horaGeneracion,
            'resumen' => [
                'total_inscritos' => $inscripciones->count(),
                'presentes' => $asistencias->where('estado', 'presente')->count(),
                'ausentes' => $asistencias->where('estado', 'ausente')->count(),
                'tardanzas' => $asistencias->where('estado', 'tardanza')->count(),
                'justificados' => $asistencias->where('estado', 'justificado')->count(),
                'sin_registrar' => $inscripciones->count() - $asistencias->count(),
            ],
            'lista' => $lista,
        ]);
    }

    /**
     * Generar reporte de asistencia (alias de exportar para compatibilidad)
     */
    public function reporte(Request $request, $idGrupo)
    {
        $grupo = GrupoCurso::with(['curso', 'instructor'])->findOrFail($idGrupo);
        
        $user = Auth::user();
        if ($user->isInstructor() && $grupo->id_instructor !== $user->id_usuario) {
            return response()->json([
                'message' => 'No tienes acceso a este grupo'
            ], 403);
        }

        $fecha = $request->get('fecha', Carbon::now('America/Guayaquil')->toDateString());

        // Obtener deportistas inscritos
        $inscripciones = InscripcionCurso::with('deportista')
            ->where('id_grupo', $idGrupo)
            ->where('estado', 'activa')
            ->get();

        // Obtener asistencias del día
        $asistencias = Asistencia::where('id_grupo', $idGrupo)
            ->whereDate('fecha', $fecha)
            ->get()
            ->keyBy('id_deportista');

        $estudiantes = $inscripciones->map(function($inscripcion) use ($asistencias) {
            $asistencia = $asistencias->get($inscripcion->id_deportista);
            return [
                'nombre' => $inscripcion->deportista->nombres . ' ' . $inscripcion->deportista->apellidos,
                'cedula' => $inscripcion->deportista->cedula ?? '-',
                'estado' => $asistencia ? ucfirst($asistencia->estado) : 'Sin registrar',
                'observaciones' => $asistencia?->observaciones ?? '',
            ];
        })->sortBy('nombre')->values();

        $fechaFormato = Carbon::parse($fecha)->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY');

        return response()->json([
            'titulo' => 'Reporte de Asistencia',
            'grupo' => $grupo->nombre,
            'curso' => $grupo->curso->nombre,
            'instructor' => $grupo->instructor ? $grupo->instructor->nombre_completo : 'Sin asignar',
            'fecha' => $fechaFormato,
            'horario' => $grupo->hora_inicio . ' - ' . $grupo->hora_fin,
            'generado_en' => Carbon::now('America/Guayaquil')->format('d/m/Y H:i'),
            'generado_por' => $user->nombre_completo,
            'resumen' => [
                'total_estudiantes' => $inscripciones->count(),
                'presentes' => $asistencias->where('estado', 'presente')->count(),
                'ausentes' => $asistencias->where('estado', 'ausente')->count(),
                'tardanzas' => $asistencias->where('estado', 'tardanza')->count(),
                'justificados' => $asistencias->where('estado', 'justificado')->count(),
                'sin_registrar' => $inscripciones->count() - $asistencias->count(),
            ],
            'estudiantes' => $estudiantes,
        ]);
    }
}
