<?php

namespace App\Http\Controllers\API\Deportistas;  

use App\Http\Controllers\Controller;
use App\Http\Requests\DeportistaRequest;
use App\Models\Deportista;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DeportistaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Deportista::with(['categoria']);

        // Filtros
        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('genero')) {
            $query->where('genero', $request->genero);
        }

        if ($request->has('id_categoria')) {
            $query->where('id_categoria', $request->id_categoria);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombres', 'like', "%{$search}%")
                  ->orWhere('apellidos', 'like', "%{$search}%")
                  ->orWhere('numero_documento', 'like', "%{$search}%")
                  ->orWhere('correo', 'like', "%{$search}%");
            });
        }

        // Ordenamiento
        $sortField = $request->get('sort_by', 'apellidos');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortField, $sortOrder);

        // Paginación
        $perPage = $request->get('per_page', 15);
        $deportistas = $query->paginate($perPage);

        // Formatear datos manualmente
        $deportistas->getCollection()->transform(function ($deportista) {
            return $this->formatDeportista($deportista);
        });

        return response()->json([
            'success' => true,
            'data' => $deportistas,
            'message' => 'Lista de deportistas obtenida exitosamente'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DeportistaRequest $request)
    {
        try {
            $data = $request->validated();
            $data['created_by'] = Auth::id();

            // Manejar la carga de foto
            if ($request->hasFile('foto')) {
                $data['foto'] = $request->file('foto')->store('deportistas/fotos', 'public');
            }

            $deportista = Deportista::create($data);
            $deportista->load(['categoria']);

            return response()->json([
                'success' => true,
                'data' => $this->formatDeportista($deportista),
                'message' => 'Deportista creado exitosamente'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el deportista: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Deportista $deportista)
    {
        $deportista->load(['categoria', 'creador']);
        
        return response()->json([
            'success' => true,
            'data' => $this->formatDeportista($deportista, true),
            'message' => 'Deportista obtenido exitosamente'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DeportistaRequest $request, Deportista $deportista)
    {
        try {
            $data = $request->validated();
            $data['updated_by'] = Auth::id();

            // Manejar la actualización de foto
            if ($request->hasFile('foto')) {
                // Eliminar foto anterior si existe
                if ($deportista->foto && Storage::disk('public')->exists($deportista->foto)) {
                    Storage::disk('public')->delete($deportista->foto);
                }
                $data['foto'] = $request->file('foto')->store('deportistas/fotos', 'public');
            }

            $deportista->update($data);
            $deportista->load(['categoria']);

            return response()->json([
                'success' => true,
                'data' => $this->formatDeportista($deportista),
                'message' => 'Deportista actualizado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el deportista: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Deportista $deportista)
    {
        try {
            if ($deportista->estado !== 'retirado') {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar un deportista que no está retirado'
                ], 422);
            }

            $deportista->deleted_by = Auth::id();
            $deportista->save();
            $deportista->delete();

            return response()->json([
                'success' => true,
                'message' => 'Deportista eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el deportista: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restaurar un deportista eliminado
     */
    public function restore($id)
    {
        try {
            $deportista = Deportista::withTrashed()->findOrFail($id);
            
            if ($deportista->trashed()) {
                $deportista->restore();
                $deportista->deleted_by = null;
                $deportista->save();
                $deportista->load(['categoria']);
            }

            return response()->json([
                'success' => true,
                'data' => $this->formatDeportista($deportista),
                'message' => 'Deportista restaurado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al restaurar el deportista: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambiar estado del deportista
     */
    public function cambiarEstado(Request $request, Deportista $deportista)
    {
        $request->validate([
            'estado' => 'required|in:activo,lesionado,suspendido,retirado'
        ]);

        try {
            $deportista->update([
                'estado' => $request->estado,
                'updated_by' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'data' => $this->formatDeportista($deportista),
                'message' => 'Estado actualizado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar el estado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener deportistas activos
     */
    public function activos(Request $request)
    {
        $query = Deportista::activos()->with(['categoria']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombres', 'like', "%{$search}%")
                  ->orWhere('apellidos', 'like', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 15);
        $deportistas = $query->paginate($perPage);

        // Formatear datos
        $deportistas->getCollection()->transform(function ($deportista) {
            return $this->formatDeportista($deportista);
        });

        return response()->json([
            'success' => true,
            'data' => $deportistas,
            'message' => 'Deportistas activos obtenidos exitosamente'
        ]);
    }

    /**
     * Obtener estadísticas de deportistas
     */
    public function estadisticas()
    {
        try {
            $total = Deportista::count();
            $activos = Deportista::activos()->count();
            $lesionados = Deportista::lesionados()->count();
            $suspendidos = Deportista::suspendidos()->count();
            $retirados = Deportista::retirados()->count();
            $masculinos = Deportista::porGenero('masculino')->count();
            $femeninos = Deportista::porGenero('femenino')->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $total,
                    'por_estado' => [
                        'activos' => $activos,
                        'lesionados' => $lesionados,
                        'suspendidos' => $suspendidos,
                        'retirados' => $retirados
                    ],
                    'por_genero' => [
                        'masculinos' => $masculinos,
                        'femeninos' => $femeninos
                    ],
                    'porcentajes' => [
                        'activos' => $total > 0 ? round(($activos / $total) * 100, 2) : 0,
                        'lesionados' => $total > 0 ? round(($lesionados / $total) * 100, 2) : 0,
                        'masculinos' => $total > 0 ? round(($masculinos / $total) * 100, 2) : 0,
                        'femeninos' => $total > 0 ? round(($femeninos / $total) * 100, 2) : 0
                    ]
                ],
                'message' => 'Estadísticas obtenidas exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    // ========== MÉTODOS PARA TUTORES (Mis Participantes) ==========

    /**
     * Obtener los participantes (hijos) del tutor autenticado
     */
    public function misParticipantes(Request $request)
    {
        $user = Auth::user();
        
        // El usuario autenticado ES el tutor (tiene rol tutor)
        $query = Deportista::whereHas('tutores', function($q) use ($user) {
            $q->where('deportista_tutores.id_usuario', $user->id_usuario);
        })->with(['categoria', 'inscripciones.curso', 'inscripciones.grupo']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombres', 'like', "%{$search}%")
                  ->orWhere('apellidos', 'like', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 15);
        $deportistas = $query->paginate($perPage);

        return response()->json($deportistas);
    }

    /**
     * Crear un nuevo participante (hijo) para el tutor autenticado
     */
    public function crearMiParticipante(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'fecha_nacimiento' => 'required|date|before:today',
            'genero' => 'required|in:M,F,masculino,femenino,otro',
            'cedula' => 'nullable|string|max:20',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'direccion' => 'nullable|string',
            'tipo_sangre' => 'nullable|string|max:10',
            'alergias' => 'nullable|string',
            'enfermedades' => 'nullable|string',
            'id_categoria' => 'nullable|exists:categorias,id_categoria',
        ]);

        try {
            // Convertir género de M/F a masculino/femenino
            $genero = $request->genero;
            if ($genero === 'M') {
                $genero = 'masculino';
            } elseif ($genero === 'F') {
                $genero = 'femenino';
            }

            $deportista = Deportista::create([
                'nombres' => $request->nombres,
                'apellidos' => $request->apellidos,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'genero' => $genero,
                'cedula' => $request->cedula,
                'telefono' => $request->telefono,
                'email' => $request->email,
                'direccion' => $request->direccion,
                'tipo_sangre' => $request->tipo_sangre,
                'alergias' => $request->alergias,
                'enfermedades' => $request->enfermedades,
                'id_categoria' => $request->id_categoria,
                'estado' => 'activo',
                'created_by' => $user->id_usuario
            ]);

            // Asociar al tutor (el usuario autenticado)
            $deportista->tutores()->attach($user->id_usuario, ['es_principal' => true]);

            return response()->json([
                'message' => 'Participante registrado exitosamente',
                'data' => $deportista->load(['categoria', 'tutores'])
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al registrar participante',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar un participante del tutor autenticado
     */
    public function actualizarMiParticipante(Request $request, $id)
    {
        $user = Auth::user();

        // Verificar que el deportista pertenece al tutor (usuario autenticado)
        $deportista = Deportista::whereHas('tutores', function($q) use ($user) {
            $q->where('deportista_tutores.id_usuario', $user->id_usuario);
        })->find($id);

        if (!$deportista) {
            return response()->json(['message' => 'Participante no encontrado'], 404);
        }

        $request->validate([
            'nombres' => 'sometimes|string|max:100',
            'apellidos' => 'sometimes|string|max:100',
            'fecha_nacimiento' => 'sometimes|date|before:today',
            'genero' => 'sometimes|in:M,F,masculino,femenino,otro',
            'cedula' => 'nullable|string|max:20',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'direccion' => 'nullable|string',
            'tipo_sangre' => 'nullable|string|max:10',
            'alergias' => 'nullable|string',
            'enfermedades' => 'nullable|string',
            'id_categoria' => 'nullable|exists:categorias,id_categoria',
        ]);

        // Convertir género de M/F a masculino/femenino si es necesario
        $data = $request->only([
            'nombres', 'apellidos', 'fecha_nacimiento', 'genero',
            'cedula', 'telefono', 'email', 'direccion',
            'tipo_sangre', 'alergias', 'enfermedades', 'id_categoria'
        ]);

        if (isset($data['genero'])) {
            if ($data['genero'] === 'M') {
                $data['genero'] = 'masculino';
            } elseif ($data['genero'] === 'F') {
                $data['genero'] = 'femenino';
            }
        }

        $deportista->update($data);

        return response()->json([
            'message' => 'Participante actualizado exitosamente',
            'data' => $deportista->load('categoria')
        ]);
    }

    /**
     * Eliminar un participante del tutor autenticado
     */
    public function eliminarMiParticipante($id)
    {
        $user = Auth::user();

        $deportista = Deportista::whereHas('tutores', function($q) use ($user) {
            $q->where('deportista_tutores.id_usuario', $user->id_usuario);
        })->find($id);

        if (!$deportista) {
            return response()->json(['message' => 'Participante no encontrado'], 404);
        }

        // Verificar que no tenga inscripciones activas
        $inscripcionesActivas = $deportista->inscripciones()->where('estado', 'activa')->count();
        if ($inscripcionesActivas > 0) {
            return response()->json([
                'message' => 'No se puede eliminar un participante con inscripciones activas'
            ], 400);
        }

        $deportista->delete();

        return response()->json([
            'message' => 'Participante eliminado exitosamente'
        ]);
    }

    /**
     * Método privado para formatear deportista
     */
    private function formatDeportista($deportista, $full = false)
    {
        $formatted = [
            'id_deportista' => $deportista->id_deportista,
            'id_categoria' => $deportista->id_categoria,
            'nombres' => $deportista->nombres,
            'apellidos' => $deportista->apellidos,
            'nombre_completo' => $deportista->nombre_completo,
            'cedula' => $deportista->cedula,
            'fecha_nacimiento' => $deportista->fecha_nacimiento ? $deportista->fecha_nacimiento->format('Y-m-d') : null,
            'edad' => $deportista->edad,
            'genero' => $deportista->genero,
            'foto' => $deportista->foto,
            'foto_url' => $deportista->foto_url,
            'direccion' => $deportista->direccion,
            'email' => $deportista->email,
            'telefono' => $deportista->telefono,
            'altura' => $deportista->altura,
            'peso' => $deportista->peso,
            'tipo_sangre' => $deportista->tipo_sangre,
            'alergias' => $deportista->alergias,
            'enfermedades' => $deportista->enfermedades,
            'medicamentos' => $deportista->medicamentos,
            'contacto_emergencia' => $deportista->contacto_emergencia,
            'telefono_emergencia' => $deportista->telefono_emergencia,
            'estado' => $deportista->estado,
            'created_at' => $deportista->created_at ? $deportista->created_at->format('Y-m-d H:i:s') : null,
            'updated_at' => $deportista->updated_at ? $deportista->updated_at->format('Y-m-d H:i:s') : null,
        ];

        if ($deportista->relationLoaded('categoria')) {
            $formatted['categoria'] = $deportista->categoria;
        }

        if ($full) {
            $formatted['deleted_at'] = $deportista->deleted_at ? $deportista->deleted_at->format('Y-m-d H:i:s') : null;
            
            if ($deportista->relationLoaded('creador')) {
                $formatted['creador'] = $deportista->creador;
            }
        }

        return $formatted;
    }
}