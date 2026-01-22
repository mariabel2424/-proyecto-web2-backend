<?php
namespace App\Http\Controllers\API\Cursos;
use App\Http\Controllers\Controller;

use App\Models\GrupoCurso;
use App\Models\Curso;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GrupoCursoController extends Controller
{
    public function index(Request $request)
    {
        $query = GrupoCurso::with('curso', 'instructor');

        // Filtro por curso
        if ($request->has('id_curso')) {
            $query->where('id_curso', $request->id_curso);
        }

        // Filtro por estado
        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        // Solo grupos activos
        if ($request->has('activos')) {
            $query->activos();
        }

        // Solo grupos con cupo disponible
        if ($request->has('con_cupo')) {
            $query->conCupoDisponible();
        }

        // Búsqueda por nombre
        if ($request->has('buscar')) {
            $query->where('nombre', 'like', '%' . $request->buscar . '%');
        }

        $grupos = $query->orderBy('nombre', 'asc')->paginate(15);
        return response()->json($grupos);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_curso' => 'required|exists:cursos,id_curso',
            'nombre' => 'required|string|max:100',
            'cupo_maximo' => 'required|integer|min:1',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i',
            'dias_semana' => 'required|array|min:1',
            'dias_semana.*' => 'in:1,2,3,4,5,6,7,lunes,martes,miércoles,miercoles,jueves,viernes,sábado,sabado,domingo',
            'id_instructor' => 'nullable|exists:usuarios,id_usuario',
            'estado' => 'sometimes|in:activo,inactivo,completo,cancelado'
        ]);

        // Validar que hora_fin sea después de hora_inicio
        if ($request->hora_inicio >= $request->hora_fin) {
            return response()->json([
                'message' => 'La hora de fin debe ser posterior a la hora de inicio',
                'errors' => ['hora_fin' => ['La hora de fin debe ser posterior a la hora de inicio']]
            ], 422);
        }

        // Verificar que el curso exista
        $curso = Curso::findOrFail($request->id_curso);

        $grupo = GrupoCurso::create([
            'id_curso' => $request->id_curso,
            'nombre' => $request->nombre,
            'cupo_maximo' => $request->cupo_maximo,
            'cupo_actual' => 0,
            'hora_inicio' => $request->hora_inicio,
            'hora_fin' => $request->hora_fin,
            'dias_semana' => $request->dias_semana,
            'id_instructor' => $request->id_instructor,
            'estado' => $request->estado ?? 'activo',
            'created_by' => Auth::id()
        ]);

        return response()->json([
            'message' => 'Grupo creado exitosamente',
            'data' => $grupo->load('curso', 'instructor')
        ], 201);
    }

    public function show($id)
    {
        $grupo = GrupoCurso::with('curso', 'instructor', 'inscripciones.deportista')
                          ->findOrFail($id);
        
        return response()->json($grupo);
    }

    public function update(Request $request, $id)
    {
        $grupo = GrupoCurso::findOrFail($id);

        $request->validate([
            'nombre' => 'sometimes|string|max:100',
            'cupo_maximo' => 'sometimes|integer|min:1',
            'hora_inicio' => 'sometimes|date_format:H:i',
            'hora_fin' => 'sometimes|date_format:H:i',
            'dias_semana' => 'sometimes|array|min:1',
            'dias_semana.*' => 'in:1,2,3,4,5,6,7,lunes,martes,miércoles,miercoles,jueves,viernes,sábado,sabado,domingo',
            'id_instructor' => 'nullable|exists:usuarios,id_usuario',
            'estado' => 'sometimes|in:activo,inactivo,completo,cancelado'
        ]);

        // Validar que hora_fin sea después de hora_inicio si ambas están presentes
        $horaInicio = $request->hora_inicio ?? $grupo->hora_inicio;
        $horaFin = $request->hora_fin ?? $grupo->hora_fin;
        if ($horaInicio && $horaFin && $horaInicio >= $horaFin) {
            return response()->json([
                'message' => 'La hora de fin debe ser posterior a la hora de inicio',
                'errors' => ['hora_fin' => ['La hora de fin debe ser posterior a la hora de inicio']]
            ], 422);
        }

        // Validar que el cupo máximo no sea menor al cupo actual
        if ($request->has('cupo_maximo') && $request->cupo_maximo < $grupo->cupo_actual) {
            return response()->json([
                'message' => 'El cupo máximo no puede ser menor al cupo actual (' . $grupo->cupo_actual . ')'
            ], 400);
        }

        $grupo->update($request->all());

        // Si se aumentó el cupo y el grupo estaba completo, activarlo
        if ($request->has('cupo_maximo') && $grupo->cupo_actual < $grupo->cupo_maximo && $grupo->estado === 'completo') {
            $grupo->update(['estado' => 'activo']);
        }

        return response()->json([
            'message' => 'Grupo actualizado exitosamente',
            'data' => $grupo->load('instructor')
        ]);
    }

    public function destroy($id)
    {
        $grupo = GrupoCurso::findOrFail($id);
        
        // Verificar si tiene inscripciones activas
        $inscripcionesActivas = $grupo->inscripciones()->where('estado', 'activa')->count();
        
        if ($inscripcionesActivas > 0) {
            return response()->json([
                'message' => 'No se puede eliminar el grupo porque tiene ' . $inscripcionesActivas . ' inscripciones activas'
            ], 400);
        }

        $grupo->delete();

        return response()->json([
            'message' => 'Grupo eliminado exitosamente'
        ]);
    }

    // Listar deportistas inscritos en el grupo
    public function deportistas($id)
    {
        $grupo = GrupoCurso::with(['inscripciones' => function($query) {
            $query->where('estado', 'activa')->with('deportista');
        }])->findOrFail($id);

        return response()->json([
            'grupo' => $grupo->nombre,
            'curso' => $grupo->curso->nombre,
            'cupo_actual' => $grupo->cupo_actual,
            'cupo_maximo' => $grupo->cupo_maximo,
            'cupos_disponibles' => $grupo->cupos_disponibles,
            'total_deportistas' => $grupo->inscripciones->count(),
            'deportistas' => $grupo->inscripciones
        ]);
    }

    // Asignar instructor al grupo
    public function asignarInstructor(Request $request, $id)
    {
        $request->validate([
            'id_instructor' => 'required|exists:usuarios,id_usuario'
        ]);

        $grupo = GrupoCurso::findOrFail($id);

        // Verificar que el usuario sea instructor
        $instructor = Usuario::instructores()->find($request->id_instructor);
        if (!$instructor) {
            return response()->json([
                'message' => 'El usuario seleccionado no es un instructor'
            ], 400);
        }

        $grupo->update(['id_instructor' => $request->id_instructor]);

        return response()->json([
            'message' => 'Instructor asignado exitosamente al grupo',
            'data' => $grupo->load('instructor')
        ]);
    }

    // Quitar instructor del grupo
    public function quitarInstructor(Request $request, $id)
    {
        $grupo = GrupoCurso::findOrFail($id);

        if (!$grupo->id_instructor) {
            return response()->json([
                'message' => 'Este grupo no tiene instructor asignado'
            ], 400);
        }

        $grupo->update(['id_instructor' => null]);

        return response()->json([
            'message' => 'Instructor removido exitosamente del grupo'
        ]);
    }

    // Listar instructor del grupo
    public function instructores($id)
    {
        $grupo = GrupoCurso::with('instructor')->findOrFail($id);

        // Retornar en formato compatible con frontend (array de instructores)
        $instructores = $grupo->instructor ? [$grupo->instructor] : [];

        return response()->json([
            'grupo' => $grupo->nombre,
            'total_instructores' => count($instructores),
            'instructores' => $instructores
        ]);
    }

    // Cambiar estado del grupo
    public function cambiarEstado(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:activo,inactivo,completo,cancelado'
        ]);

        $grupo = GrupoCurso::findOrFail($id);
        $grupo->estado = $request->estado;
        $grupo->save();

        return response()->json([
            'message' => 'Estado del grupo actualizado exitosamente',
            'estado' => $grupo->estado,
            'data' => $grupo
        ]);
    }

    // Grupos disponibles para inscripción de un curso
    public function gruposDisponibles($idCurso)
    {
        $grupos = GrupoCurso::where('id_curso', $idCurso)
                           ->activos()
                           ->conCupoDisponible()
                           ->with('instructor')
                           ->get();

        return response()->json([
            'curso_id' => $idCurso,
            'total_grupos' => $grupos->count(),
            'grupos' => $grupos
        ]);
    }

    // Grupos públicos (para landing page sin autenticación)
    public function gruposPublico($idCurso)
    {
        $grupos = GrupoCurso::where('id_curso', $idCurso)
                           ->where('estado', 'activo')
                           ->select('id_grupo', 'id_curso', 'nombre', 'cupo_maximo', 'cupo_actual', 'hora_inicio', 'hora_fin', 'dias_semana')
                           ->get()
                           ->map(function($grupo) {
                               return [
                                   'id_grupo' => $grupo->id_grupo,
                                   'nombre' => $grupo->nombre,
                                   'cupo_maximo' => $grupo->cupo_maximo,
                                   'cupo_actual' => $grupo->cupo_actual,
                                   'cupos_disponibles' => $grupo->cupo_maximo - $grupo->cupo_actual,
                                   'hora_inicio' => $grupo->hora_inicio,
                                   'hora_fin' => $grupo->hora_fin,
                                   'dias_semana' => $grupo->dias_semana,
                               ];
                           });

        return response()->json($grupos);
    }
}
