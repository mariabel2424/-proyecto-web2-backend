<?php
namespace App\Http\Controllers\API\Deportistas; 
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Http\Request;

/**
 * InstructorController - ARQUITECTURA SIMPLIFICADA
 * 
 * Un usuario con rol "instructor" ES el instructor.
 * No se usa tabla separada de instructores.
 */
class InstructorController extends Controller
{
    /**
     * Listar todos los usuarios con rol instructor
     */
    public function index(Request $request)
    {
        $query = Usuario::with('rol')
            ->instructores(); // Scope que filtra por rol instructor

        // Filtro por estado
        if ($request->has('activo')) {
            $activo = filter_var($request->activo, FILTER_VALIDATE_BOOLEAN);
            $query->where('status', $activo ? 'activo' : 'inactivo');
        }

        // Filtro por especialidad
        if ($request->has('especialidad') && !empty($request->especialidad)) {
            $query->where('especialidad', 'like', "%{$request->especialidad}%");
        }

        // Búsqueda general
        if ($request->has('buscar') && !empty($request->buscar)) {
            $query->buscar($request->buscar);
        }

        $instructores = $query->orderBy('nombre', 'asc')->paginate(15);
        
        // Transformar para mantener compatibilidad con frontend
        $instructores->getCollection()->transform(function ($usuario) {
            return $this->transformToInstructor($usuario);
        });

        return response()->json($instructores);
    }

    /**
     * Crear un nuevo instructor (usuario con rol instructor)
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'email' => 'required|email|unique:usuarios,email',
            'telefono' => 'nullable|string|max:20',
            'especialidad' => 'required|string|max:100',
            'certificaciones' => 'nullable|string',
            'password' => 'nullable|string|min:6',
        ]);

        // Obtener el rol de instructor
        $rolInstructor = Rol::where('slug', 'instructor')->first();
        if (!$rolInstructor) {
            return response()->json([
                'message' => 'No se encontró el rol de instructor en el sistema'
            ], 400);
        }

        $usuario = Usuario::create([
            'id_rol' => $rolInstructor->id_rol,
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'email' => $request->email,
            'telefono' => $request->telefono,
            'especialidad' => $request->especialidad,
            'certificaciones' => $request->certificaciones,
            'password' => bcrypt($request->password ?? 'instructor123'),
            'status' => 'activo',
        ]);

        return response()->json([
            'message' => 'Instructor registrado exitosamente',
            'data' => $this->transformToInstructor($usuario->load('rol'))
        ], 201);
    }

    /**
     * Ver un instructor específico
     */
    public function show($id)
    {
        $usuario = Usuario::with(['rol', 'gruposComoInstructor.curso'])
            ->instructores()
            ->findOrFail($id);
        
        return response()->json($this->transformToInstructor($usuario));
    }

    /**
     * Actualizar un instructor
     */
    public function update(Request $request, $id)
    {
        $usuario = Usuario::instructores()->findOrFail($id);

        $request->validate([
            'nombre' => 'sometimes|string|max:100',
            'apellido' => 'sometimes|string|max:100',
            'email' => 'sometimes|email|unique:usuarios,email,' . $id . ',id_usuario',
            'telefono' => 'nullable|string|max:20',
            'especialidad' => 'sometimes|string|max:100',
            'certificaciones' => 'nullable|string',
        ]);

        $usuario->update($request->only([
            'nombre', 'apellido', 'email', 'telefono', 
            'especialidad', 'certificaciones'
        ]));

        return response()->json([
            'message' => 'Instructor actualizado exitosamente',
            'data' => $this->transformToInstructor($usuario)
        ]);
    }

    /**
     * Eliminar un instructor
     */
    public function destroy($id)
    {
        $usuario = Usuario::instructores()->findOrFail($id);
        
        // Verificar si tiene grupos asignados
        if ($usuario->gruposComoInstructor()->count() > 0) {
            return response()->json([
                'message' => 'No se puede eliminar el instructor porque tiene grupos asignados.'
            ], 400);
        }

        $usuario->delete();

        return response()->json([
            'message' => 'Instructor eliminado exitosamente'
        ]);
    }

    /**
     * Listar grupos del instructor
     */
    public function grupos($id)
    {
        $usuario = Usuario::with(['gruposComoInstructor.curso'])
            ->instructores()
            ->findOrFail($id);

        $grupos = $usuario->gruposComoInstructor->map(function($grupo) {
            return [
                'id_grupo' => $grupo->id_grupo,
                'nombre' => $grupo->nombre,
                'curso' => $grupo->curso->nombre ?? 'Sin curso',
                'horario' => ($grupo->hora_inicio ?? '') . ' - ' . ($grupo->hora_fin ?? ''),
                'dias_semana' => $grupo->dias_semana_nombres,
                'cupo_actual' => $grupo->cupo_actual,
                'cupo_maximo' => $grupo->cupo_maximo,
                'estado' => $grupo->estado
            ];
        });

        return response()->json([
            'instructor' => $usuario->nombre_completo,
            'especialidad' => $usuario->especialidad,
            'total_grupos' => $grupos->count(),
            'grupos' => $grupos
        ]);
    }

    /**
     * Listar instructores disponibles (activos)
     */
    public function disponibles(Request $request)
    {
        $query = Usuario::with('rol')
            ->instructores()
            ->activos();

        // Filtro por especialidad
        if ($request->has('especialidad') && !empty($request->especialidad)) {
            $query->where('especialidad', 'like', "%{$request->especialidad}%");
        }

        $instructores = $query->get()->map(function($usuario) {
            return $this->transformToInstructor($usuario);
        });

        return response()->json([
            'total' => $instructores->count(),
            'instructores' => $instructores
        ]);
    }

    /**
     * Activar/Desactivar instructor
     */
    public function toggleActivo($id)
    {
        $usuario = Usuario::instructores()->findOrFail($id);
        $usuario->status = $usuario->status === 'activo' ? 'inactivo' : 'activo';
        $usuario->save();

        return response()->json([
            'message' => 'Estado del instructor actualizado exitosamente',
            'activo' => $usuario->status === 'activo',
            'data' => $this->transformToInstructor($usuario)
        ]);
    }

    /**
     * Obtener los grupos del instructor autenticado
     */
    public function misGrupos(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isInstructor()) {
            return response()->json([
                'message' => 'No tienes rol de instructor',
                'grupos' => []
            ], 200);
        }

        $grupos = $user->gruposComoInstructor()
            ->with(['curso', 'inscripciones.deportista'])
            ->get()
            ->map(function($grupo) {
                return [
                    'id_grupo' => $grupo->id_grupo,
                    'nombre' => $grupo->nombre,
                    'curso' => [
                        'id_curso' => $grupo->curso->id_curso,
                        'nombre' => $grupo->curso->nombre,
                        'fecha_inicio' => $grupo->curso->fecha_inicio,
                        'fecha_fin' => $grupo->curso->fecha_fin,
                    ],
                    'horario' => ($grupo->hora_inicio ?? '') . ' - ' . ($grupo->hora_fin ?? ''),
                    'dias_semana' => $grupo->dias_semana_nombres,
                    'cupo_actual' => $grupo->cupo_actual,
                    'cupo_maximo' => $grupo->cupo_maximo,
                    'estado' => $grupo->estado,
                    'participantes' => $grupo->inscripciones->where('estado', 'activa')->count()
                ];
            });

        return response()->json([
            'instructor' => $user->nombre_completo,
            'especialidad' => $user->especialidad,
            'total_grupos' => $grupos->count(),
            'grupos' => $grupos
        ]);
    }

    /**
     * Transformar Usuario a formato de Instructor para compatibilidad
     */
    private function transformToInstructor($usuario)
    {
        return [
            'id_instructor' => $usuario->id_usuario,
            'id_usuario' => $usuario->id_usuario,
            'nombre_completo' => $usuario->nombre_completo,
            'nombres' => $usuario->nombre,
            'apellidos' => $usuario->apellido,
            'email' => $usuario->email,
            'telefono' => $usuario->telefono,
            'especialidad' => $usuario->especialidad,
            'certificaciones' => $usuario->certificaciones,
            'activo' => $usuario->status === 'activo',
            'usuario' => [
                'id_usuario' => $usuario->id_usuario,
                'nombre' => $usuario->nombre,
                'apellido' => $usuario->apellido,
                'email' => $usuario->email,
            ],
            'grupos' => $usuario->gruposComoInstructor ?? [],
        ];
    }
}
