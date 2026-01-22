<?php
namespace App\Http\Controllers\API\Deportistas;
use App\Http\Controllers\Controller;

use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * TutorController - ARQUITECTURA SIMPLIFICADA
 * 
 * Un usuario con rol "tutor" ES el tutor.
 * No se usa tabla separada de tutores.
 */
class TutorController extends Controller
{
    /**
     * Listar todos los usuarios con rol tutor
     */
    public function index(Request $request)
    {
        try {
            $query = Usuario::with('rol')
                ->tutores(); // Scope que filtra por rol tutor

            // Búsqueda general
            if ($request->has('buscar') && !empty($request->buscar)) {
                $query->buscar($request->buscar);
            }

            // Filtro por estado
            if ($request->has('activo') && $request->activo !== '') {
                $activo = filter_var($request->activo, FILTER_VALIDATE_BOOLEAN);
                $query->where('status', $activo ? 'activo' : 'inactivo');
            }

            // Filtro por parentesco
            if ($request->has('parentesco') && !empty($request->parentesco)) {
                $query->where('parentesco', $request->parentesco);
            }

            $tutores = $query->orderBy('apellido', 'asc')
                            ->orderBy('nombre', 'asc')
                            ->paginate(15);
            
            // Transformar para mantener compatibilidad con frontend
            $tutores->getCollection()->transform(function ($usuario) {
                return $this->transformToTutor($usuario);
            });

            return response()->json([
                'success' => true,
                'data' => $tutores,
                'message' => 'Tutores obtenidos exitosamente'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error en TutorController@index: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Crear un nuevo tutor (usuario con rol tutor)
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'cedula' => 'required|string|max:20|unique:usuarios,cedula',
            'telefono' => 'required|string|max:20',
            'email' => 'required|email|unique:usuarios,email',
            'direccion' => 'nullable|string',
            'parentesco' => 'required|in:padre,madre,abuelo,abuela,tio,tia,hermano,hermana,tutor_legal,otro',
            'password' => 'nullable|string|min:6',
        ]);

        // Obtener el rol de tutor
        $rolTutor = Rol::where('slug', 'tutor')->first();
        if (!$rolTutor) {
            return response()->json([
                'message' => 'No se encontró el rol de tutor en el sistema'
            ], 400);
        }

        $usuario = Usuario::create([
            'id_rol' => $rolTutor->id_rol,
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'email' => $request->email,
            'telefono' => $request->telefono,
            'direccion' => $request->direccion,
            'cedula' => $request->cedula,
            'parentesco' => $request->parentesco,
            'password' => bcrypt($request->password ?? 'tutor123'),
            'status' => 'activo',
        ]);

        return response()->json([
            'message' => 'Tutor registrado exitosamente',
            'data' => $this->transformToTutor($usuario->load('rol'))
        ], 201);
    }

    /**
     * Ver un tutor específico
     */
    public function show($id)
    {
        $usuario = Usuario::with(['rol', 'deportistasACargo'])
            ->tutores()
            ->findOrFail($id);
        
        return response()->json($this->transformToTutor($usuario));
    }

    /**
     * Actualizar un tutor
     */
    public function update(Request $request, $id)
    {
        $usuario = Usuario::tutores()->findOrFail($id);

        $request->validate([
            'nombre' => 'sometimes|string|max:100',
            'apellido' => 'sometimes|string|max:100',
            'cedula' => 'sometimes|string|max:20|unique:usuarios,cedula,' . $id . ',id_usuario',
            'telefono' => 'sometimes|string|max:20',
            'email' => 'sometimes|email|unique:usuarios,email,' . $id . ',id_usuario',
            'direccion' => 'nullable|string',
            'parentesco' => 'sometimes|in:padre,madre,abuelo,abuela,tio,tia,hermano,hermana,tutor_legal,otro',
        ]);

        $usuario->update($request->only([
            'nombre', 'apellido', 'cedula', 'telefono', 
            'email', 'direccion', 'parentesco'
        ]));

        return response()->json([
            'message' => 'Tutor actualizado exitosamente',
            'data' => $this->transformToTutor($usuario)
        ]);
    }

    /**
     * Eliminar un tutor
     */
    public function destroy($id)
    {
        $usuario = Usuario::tutores()->findOrFail($id);
        
        // Verificar si tiene deportistas asociados
        if ($usuario->deportistasACargo()->count() > 0) {
            return response()->json([
                'message' => 'No se puede eliminar el tutor porque tiene deportistas asociados'
            ], 400);
        }

        $usuario->delete();

        return response()->json([
            'message' => 'Tutor eliminado exitosamente'
        ]);
    }

    /**
     * Vincular tutor con deportista
     */
    public function vincularDeportista(Request $request, $id)
    {
        $request->validate([
            'id_deportista' => 'required|exists:deportistas,id_deportista',
            'principal' => 'sometimes|boolean'
        ]);

        $usuario = Usuario::tutores()->findOrFail($id);

        // Verificar si ya está vinculado
        if ($usuario->deportistasACargo()->where('id_deportista', $request->id_deportista)->exists()) {
            return response()->json([
                'message' => 'El deportista ya está vinculado a este tutor'
            ], 400);
        }

        // Si se marca como principal, quitar el principal anterior de ese deportista
        if ($request->principal) {
            DB::table('deportista_tutores')
                ->where('id_deportista', $request->id_deportista)
                ->update(['es_principal' => false]);
        }

        $usuario->deportistasACargo()->attach($request->id_deportista, [
            'es_principal' => $request->principal ?? false
        ]);

        return response()->json([
            'message' => 'Deportista vinculado exitosamente al tutor',
            'data' => $this->transformToTutor($usuario->load('deportistasACargo'))
        ]);
    }

    /**
     * Desvincular tutor de deportista
     */
    public function desvincularDeportista(Request $request, $id)
    {
        $request->validate([
            'id_deportista' => 'required|exists:deportistas,id_deportista'
        ]);

        $usuario = Usuario::tutores()->findOrFail($id);

        if (!$usuario->deportistasACargo()->where('id_deportista', $request->id_deportista)->exists()) {
            return response()->json([
                'message' => 'El deportista no está vinculado a este tutor'
            ], 400);
        }

        $usuario->deportistasACargo()->detach($request->id_deportista);

        return response()->json([
            'message' => 'Deportista desvinculado exitosamente del tutor'
        ]);
    }

    /**
     * Listar deportistas de un tutor
     */
    public function deportistas($id)
    {
        $usuario = Usuario::tutores()->findOrFail($id);
        $deportistas = $usuario->deportistasACargo()->withPivot('es_principal')->get();

        return response()->json([
            'tutor' => $usuario->nombre_completo,
            'total_deportistas' => $deportistas->count(),
            'deportistas' => $deportistas
        ]);
    }

    /**
     * Activar/Desactivar tutor
     */
    public function toggleActivo($id)
    {
        $usuario = Usuario::tutores()->findOrFail($id);
        $usuario->status = $usuario->status === 'activo' ? 'inactivo' : 'activo';
        $usuario->save();

        return response()->json([
            'message' => 'Estado del tutor actualizado exitosamente',
            'activo' => $usuario->status === 'activo',
            'data' => $this->transformToTutor($usuario)
        ]);
    }

    /**
     * Buscar tutor por cédula
     */
    public function buscarPorCedula(Request $request)
    {
        $request->validate([
            'cedula' => 'required|string'
        ]);

        $usuario = Usuario::with('rol', 'deportistasACargo')
            ->tutores()
            ->where('cedula', $request->cedula)
            ->first();

        if (!$usuario) {
            return response()->json([
                'message' => 'No se encontró ningún tutor con esa cédula'
            ], 404);
        }

        return response()->json($this->transformToTutor($usuario));
    }

    /**
     * Transformar Usuario a formato de Tutor para compatibilidad
     */
    private function transformToTutor($usuario)
    {
        return [
            'id_tutor' => $usuario->id_usuario,
            'id_usuario' => $usuario->id_usuario,
            'nombre_completo' => $usuario->nombre_completo,
            'nombres' => $usuario->nombre,
            'apellidos' => $usuario->apellido,
            'cedula' => $usuario->cedula,
            'telefono' => $usuario->telefono,
            'email' => $usuario->email,
            'direccion' => $usuario->direccion,
            'parentesco' => $usuario->parentesco,
            'activo' => $usuario->status === 'activo',
            'usuario' => [
                'id_usuario' => $usuario->id_usuario,
                'nombre' => $usuario->nombre,
                'apellido' => $usuario->apellido,
                'email' => $usuario->email,
            ],
            'deportistas' => $usuario->deportistasACargo ?? [],
        ];
    }
}
