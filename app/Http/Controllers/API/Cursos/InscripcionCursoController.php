<?php
namespace App\Http\Controllers\API\Cursos;

use App\Http\Controllers\Controller;
use App\Models\InscripcionCurso;
use App\Models\Curso;
use App\Models\GrupoCurso;
use App\Models\Factura;
use App\Models\DetalleFactura;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

/**
 * Controlador de Inscripciones para Cursos Vacacionales
 * 
 * Lógica de negocio:
 * - Tutor inscribe a un deportista en un grupo específico de un curso
 * - Se valida cupo disponible en el grupo
 * - Se valida que el curso esté abierto y dentro de fechas
 * - Opcionalmente se genera factura automática
 */
class InscripcionCursoController extends Controller
{
    public function index(Request $request)
    {
        $query = InscripcionCurso::with(['curso', 'grupo', 'usuario', 'deportista']);

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('id_curso')) {
            $query->where('id_curso', $request->id_curso);
        }

        if ($request->filled('id_grupo')) {
            $query->where('id_grupo', $request->id_grupo);
        }

        if ($request->filled('id_usuario')) {
            $query->where('id_usuario', $request->id_usuario);
        }

        if ($request->filled('id_deportista')) {
            $query->where('id_deportista', $request->id_deportista);
        }

        // Búsqueda por nombre de deportista
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('deportista', function($q) use ($search) {
                $q->where('nombres', 'like', "%{$search}%")
                  ->orWhere('apellidos', 'like', "%{$search}%")
                  ->orWhere('cedula', 'like', "%{$search}%");
            });
        }

        $inscripciones = $query->orderBy('fecha_inscripcion', 'desc')
                               ->paginate($request->per_page ?? 15);
        return response()->json($inscripciones);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_curso' => 'required|exists:cursos,id_curso',
            'id_grupo' => 'required|exists:grupos_curso,id_grupo',
            'id_deportista' => 'required|exists:deportistas,id_deportista',
            'observaciones' => 'nullable|string',
            'generar_factura' => 'nullable|boolean'
        ]);

        // Obtener curso y grupo
        $curso = Curso::findOrFail($request->id_curso);
        $grupo = GrupoCurso::findOrFail($request->id_grupo);

        // Validar que el curso esté activo
        if ($curso->estado !== 'activo') {
            return response()->json([
                'message' => 'El curso no esta disponible para inscripciones'
            ], 400);
        }

        // Validar fechas del curso
        if ($curso->fecha_fin && $curso->fecha_fin->isPast()) {
            return response()->json([
                'message' => 'El curso ya ha finalizado'
            ], 400);
        }

        // Validar que el grupo pertenezca al curso
        if ($grupo->id_curso != $request->id_curso) {
            return response()->json([
                'message' => 'El grupo no pertenece al curso seleccionado'
            ], 400);
        }

        // Validar que el grupo esté activo
        if ($grupo->estado !== 'activo') {
            return response()->json([
                'message' => 'El grupo no esta activo'
            ], 400);
        }

        // Validar cupo disponible
        if (!$grupo->tieneCupoDisponible()) {
            return response()->json([
                'message' => 'No hay cupos disponibles en este grupo'
            ], 400);
        }

        // Verificar si el deportista ya está inscrito en este grupo
        $existe = InscripcionCurso::where('id_grupo', $request->id_grupo)
                                  ->where('id_deportista', $request->id_deportista)
                                  ->where('estado', 'activa')
                                  ->exists();

        if ($existe) {
            return response()->json([
                'message' => 'El deportista ya esta inscrito en este grupo'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Crear inscripción
            $inscripcion = InscripcionCurso::create([
                'id_curso' => $request->id_curso,
                'id_grupo' => $request->id_grupo,
                'id_usuario' => Auth::id(),
                'id_deportista' => $request->id_deportista,
                'fecha_inscripcion' => now(),
                'observaciones' => $request->observaciones,
                'estado' => 'activa',
                'created_by' => Auth::id()
            ]);

            // Incrementar cupo del grupo
            $grupo->incrementarCupo();

            // Generar factura automáticamente si se solicita
            $factura = null;
            if ($request->generar_factura && $curso->precio > 0) {
                $factura = $this->generarFacturaInscripcion($inscripcion);
            }

            DB::commit();

            $response = [
                'message' => 'Inscripcion realizada exitosamente',
                'data' => $inscripcion->load(['curso', 'grupo', 'usuario', 'deportista'])
            ];

            if ($factura) {
                $response['factura'] = $factura;
            }

            return response()->json($response, 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al realizar inscripcion',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Genera factura para una inscripción
     */
    private function generarFacturaInscripcion(InscripcionCurso $inscripcion)
    {
        $inscripcion->load(['curso', 'grupo', 'deportista']);

        // Obtener tutor principal del deportista (ahora es id_usuario)
        $tutorPrincipal = DB::table('deportista_tutores')
            ->where('id_deportista', $inscripcion->id_deportista)
            ->where('es_principal', true)
            ->first();

        $ultimaFactura = Factura::latest('id_factura')->first();
        $numeroFactura = 'FAC-' . str_pad(($ultimaFactura ? $ultimaFactura->id_factura + 1 : 1), 8, '0', STR_PAD_LEFT);

        $factura = Factura::create([
            'id_deportista' => $inscripcion->id_deportista,
            'id_tutor' => $tutorPrincipal?->id_usuario,
            'id_inscripcion' => $inscripcion->id_inscripcion,
            'usuario_id' => Auth::id(),
            'numero_factura' => $numeroFactura,
            'concepto' => "Inscripcion: " . $inscripcion->curso->nombre,
            'fecha_emision' => now(),
            'fecha_vencimiento' => now()->addDays(15),
            'descuento' => 0,
            'impuesto' => 0,
            'subtotal' => $inscripcion->curso->precio,
            'total' => $inscripcion->curso->precio,
            'estado' => 'pendiente',
            'created_by' => Auth::id()
        ]);

        DetalleFactura::create([
            'id_factura' => $factura->id_factura,
            'concepto' => $inscripcion->curso->nombre,
            'descripcion' => "Grupo: " . $inscripcion->grupo->nombre,
            'cantidad' => 1,
            'precio_unitario' => $inscripcion->curso->precio,
            'subtotal' => $inscripcion->curso->precio,
            'descuento' => 0,
            'monto' => $inscripcion->curso->precio
        ]);

        // Extraer información de pago de las observaciones
        $observaciones = $inscripcion->observaciones ?? '';
        $metodoPago = 'transferencia';
        $referencia = null;
        $comprobanteUrl = null;

        // Extraer método de pago
        if (preg_match('/Método:\s*(\w+)/', $observaciones, $matches)) {
            $metodoPago = $matches[1];
        }

        // Extraer referencia
        if (preg_match('/Ref:\s*([^\s-]+)/', $observaciones, $matches)) {
            $referencia = $matches[1] !== 'N/A' ? $matches[1] : null;
        }

        // Extraer URL del comprobante
        if (preg_match('/Comprobante:\s*(https?:\/\/[^\s]+)/', $observaciones, $matches)) {
            $comprobanteUrl = $matches[1];
        }

        // Crear registro de pago pendiente si hay comprobante
        if ($comprobanteUrl) {
            $ultimoPago = \App\Models\Pago::latest('id_pago')->first();
            $numeroPago = 'PAG-' . str_pad(($ultimoPago ? $ultimoPago->id_pago + 1 : 1), 8, '0', STR_PAD_LEFT);

            \App\Models\Pago::create([
                'id_factura' => $factura->id_factura,
                'numero_pago' => $numeroPago,
                'monto' => $inscripcion->curso->precio,
                'fecha_pago' => now(),
                'metodo_pago' => $metodoPago,
                'referencia' => $referencia,
                'comprobante' => $comprobanteUrl,
                'estado' => 'pendiente',
                'observaciones' => 'Pago pendiente de verificación',
                'created_by' => Auth::id()
            ]);
        }

        return $factura->load('detalles', 'pagos');
    }

    public function show($id)
    {
        $inscripcion = InscripcionCurso::with([
            'curso', 
            'grupo', 
            'usuario', 
            'deportista.categoria',
            'deportista.tutores'
        ])->findOrFail($id);

        // Incluir factura si existe
        $factura = Factura::where('id_inscripcion', $id)->first();
        
        $data = $inscripcion->toArray();
        $data['factura'] = $factura;

        return response()->json($data);
    }

    public function update(Request $request, $id)
    {
        $inscripcion = InscripcionCurso::findOrFail($id);

        $request->validate([
            'estado' => 'sometimes|in:activa,completada,cancelada,abandonada',
            'calificacion' => 'nullable|numeric|min:0|max:10',
            'comentarios' => 'nullable|string',
            'observaciones' => 'nullable|string'
        ]);

        $estadoAnterior = $inscripcion->estado;
        $inscripcion->update($request->all());

        // Decrementar cupo si se cancela o abandona
        if (in_array($inscripcion->estado, ['cancelada', 'abandonada']) && $estadoAnterior === 'activa') {
            $inscripcion->grupo->decrementarCupo();
        }

        return response()->json([
            'message' => 'Inscripcion actualizada exitosamente',
            'data' => $inscripcion->load(['curso', 'grupo', 'deportista'])
        ]);
    }

    public function destroy($id)
    {
        $inscripcion = InscripcionCurso::findOrFail($id);
        
        // Decrementar cupo si estaba activa
        if ($inscripcion->estado === 'activa') {
            $inscripcion->grupo->decrementarCupo();
        }
        
        $inscripcion->delete();

        return response()->json([
            'message' => 'Inscripcion eliminada exitosamente'
        ]);
    }

    public function calificar(Request $request, $id)
    {
        $request->validate([
            'calificacion' => 'required|numeric|min:0|max:10',
            'comentarios' => 'nullable|string'
        ]);

        $inscripcion = InscripcionCurso::findOrFail($id);
        
        $inscripcion->update([
            'calificacion' => $request->calificacion,
            'comentarios' => $request->comentarios,
            'estado' => 'completada',
            'updated_by' => Auth::id()
        ]);

        return response()->json([
            'message' => 'Calificacion registrada exitosamente',
            'data' => $inscripcion->load(['curso', 'grupo', 'deportista'])
        ]);
    }

    public function cancelar(Request $request, $id)
    {
        $inscripcion = InscripcionCurso::findOrFail($id);

        if ($inscripcion->estado !== 'activa') {
            return response()->json([
                'message' => 'Solo se pueden cancelar inscripciones activas'
            ], 400);
        }

        $inscripcion->update([
            'estado' => 'cancelada',
            'observaciones' => $request->motivo ?? $inscripcion->observaciones,
            'updated_by' => Auth::id()
        ]);

        // Decrementar cupo
        $inscripcion->grupo->decrementarCupo();

        return response()->json([
            'message' => 'Inscripcion cancelada exitosamente',
            'data' => $inscripcion
        ]);
    }

    /**
     * Inscripciones de un deportista específico
     */
    public function inscripcionesDeportista($idDeportista)
    {
        $inscripciones = InscripcionCurso::with(['curso', 'grupo'])
            ->where('id_deportista', $idDeportista)
            ->orderBy('fecha_inscripcion', 'desc')
            ->get();

        return response()->json([
            'total' => $inscripciones->count(),
            'activas' => $inscripciones->where('estado', 'activa')->count(),
            'inscripciones' => $inscripciones
        ]);
    }

    /**
     * Cursos disponibles para inscripción
     */
    public function cursosDisponibles()
    {
        $cursos = Curso::with(['grupos' => function($query) {
            $query->where('estado', 'activo')
                  ->whereRaw('cupo_actual < cupo_maximo');
        }])
        ->where('estado', 'activo')
        ->where(function($q) {
            $q->whereNull('fecha_fin')
              ->orWhere('fecha_fin', '>=', now());
        })
        ->get();

        return response()->json($cursos);
    }

    /**
     * Mis inscripciones (para tutores)
     * Muestra las inscripciones de los deportistas del tutor autenticado
     */
    public function misInscripciones(Request $request)
    {
        $user = Auth::user();

        // Obtener IDs de deportistas del tutor (el usuario autenticado ES el tutor)
        $deportistaIds = DB::table('deportista_tutores')
            ->where('id_usuario', $user->id_usuario)
            ->pluck('id_deportista');

        $query = InscripcionCurso::with(['curso', 'grupo', 'deportista', 'factura'])
            ->whereIn('id_deportista', $deportistaIds);

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('id_curso')) {
            $query->where('id_curso', $request->id_curso);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('deportista', function($q2) use ($search) {
                    $q2->where('nombres', 'like', "%{$search}%")
                       ->orWhere('apellidos', 'like', "%{$search}%");
                })->orWhereHas('curso', function($q2) use ($search) {
                    $q2->where('nombre', 'like', "%{$search}%");
                });
            });
        }

        $inscripciones = $query->orderBy('fecha_inscripcion', 'desc')
                               ->paginate($request->per_page ?? 15);

        return response()->json($inscripciones);
    }

    /**
     * Generar factura para una inscripción existente (endpoint público)
     */
    public function generarFactura(Request $request, $id)
    {
        $inscripcion = InscripcionCurso::with(['curso', 'deportista'])->findOrFail($id);

        // Verificar si ya tiene factura
        $facturaExistente = Factura::where('id_inscripcion', $id)->first();
        if ($facturaExistente) {
            return response()->json([
                'message' => 'Esta inscripcion ya tiene una factura generada',
                'factura' => $facturaExistente
            ], 400);
        }

        // Verificar que el curso tenga precio
        if (!$inscripcion->curso->precio || $inscripcion->curso->precio <= 0) {
            return response()->json([
                'message' => 'El curso no tiene precio definido'
            ], 400);
        }

        $factura = $this->generarFacturaInscripcion($inscripcion);

        return response()->json([
            'message' => 'Factura generada exitosamente',
            'data' => $factura
        ], 201);
    }
}
