<?php
namespace App\Http\Controllers\API\Sistema;

use App\Http\Controllers\Controller;
use App\Models\Deportista;
use App\Models\Curso;
use App\Models\GrupoCurso;
use App\Models\InscripcionCurso;
use App\Models\Usuario;
use App\Models\Factura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function estadisticasGenerales()
    {
        $datos = [
            'cursos' => [
                'total' => Curso::count(),
                'activos' => Curso::where('estado', 'activo')->count(),
                'inactivos' => Curso::where('estado', 'inactivo')->count(),
                'finalizados' => Curso::where('estado', 'finalizado')->count(),
            ],
            'grupos' => [
                'total' => GrupoCurso::count(),
                'activos' => GrupoCurso::where('estado', 'activo')->count(),
                'completos' => GrupoCurso::where('estado', 'completo')->count(),
            ],
            'inscripciones' => [
                'total' => InscripcionCurso::count(),
                'activas' => InscripcionCurso::where('estado', 'activa')->count(),
                'completadas' => InscripcionCurso::where('estado', 'completada')->count(),
                'este_mes' => InscripcionCurso::whereMonth('fecha_inscripcion', now()->month)
                                              ->whereYear('fecha_inscripcion', now()->year)
                                              ->count(),
            ],
            'deportistas' => [
                'total' => Deportista::count(),
                'activos' => Deportista::where('estado', 'activo')->count(),
            ],
            'instructores' => [
                'total' => Usuario::instructores()->count(),
                'activos' => Usuario::instructores()->where('status', 'activo')->count(),
            ],
            'tutores' => [
                'total' => Usuario::tutores()->count(),
            ],
            'facturacion' => [
                'total_mes' => Factura::whereMonth('fecha_emision', now()->month)
                                     ->whereYear('fecha_emision', now()->year)
                                     ->sum('total'),
                'pendiente' => Factura::where('estado', 'pendiente')->sum('total'),
                'pagadas_mes' => Factura::where('estado', 'pagada')
                                       ->whereMonth('fecha_emision', now()->month)
                                       ->sum('total'),
            ]
        ];
        return response()->json($datos);
    }

    public function cursosActivos()
    {
        $cursos = Curso::withCount(['grupos', 'inscripciones' => function($q) {
                $q->where('estado', 'activa');
            }])
            ->where('estado', 'activo')
            ->orderBy('fecha_inicio', 'asc')
            ->limit(10)
            ->get()
            ->map(function($curso) {
                return [
                    'id_curso' => $curso->id_curso,
                    'nombre' => $curso->nombre,
                    'tipo' => $curso->tipo,
                    'estado' => $curso->estado,
                    'fecha_inicio' => $curso->fecha_inicio,
                    'fecha_fin' => $curso->fecha_fin,
                    'precio' => $curso->precio,
                    'cupo_maximo' => $curso->cupo_maximo,
                    'cupo_actual' => $curso->cupo_actual,
                    'grupos_count' => $curso->grupos_count,
                    'inscripciones_count' => $curso->inscripciones_count,
                    'porcentaje_ocupacion' => $curso->cupo_maximo > 0 
                        ? round(($curso->cupo_actual / $curso->cupo_maximo) * 100, 1) 
                        : 0,
                ];
            });
        return response()->json($cursos);
    }

    public function participantes(Request $request)
    {
        $limit = $request->get('limit', 100);
        $participantes = Deportista::with(['categoria', 'tutores'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($d) {
                return [
                    'id' => $d->id_deportista,
                    'nombre' => $d->nombres . ' ' . $d->apellidos,
                    'edad' => $d->edad,
                    'categoria' => $d->categoria->nombre ?? null,
                    'estado' => $d->estado,
                    'tutor' => $d->tutores->first() ? $d->tutores->first()->nombre . ' ' . $d->tutores->first()->apellido : null,
                ];
            });
        return response()->json($participantes);
    }

    public function inscripcionesRecientes()
    {
        $inscripciones = InscripcionCurso::with(['curso', 'grupo', 'deportista', 'creador'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function($inscripcion) {
                return [
                    'id_inscripcion' => $inscripcion->id_inscripcion,
                    'fecha_inscripcion' => $inscripcion->fecha_inscripcion,
                    'estado' => $inscripcion->estado,
                    'curso' => $inscripcion->curso->nombre ?? null,
                    'grupo' => $inscripcion->grupo->nombre ?? null,
                    'deportista' => $inscripcion->deportista 
                        ? $inscripcion->deportista->nombres . ' ' . $inscripcion->deportista->apellidos 
                        : null,
                    'registrado_por' => $inscripcion->creador 
                        ? $inscripcion->creador->nombre . ' ' . $inscripcion->creador->apellido 
                        : null,
                ];
            });
        return response()->json($inscripciones);
    }

    public function facturacionMensual()
    {
        $facturacion = Factura::select(
                DB::raw('MONTH(fecha_emision) as mes'),
                DB::raw('SUM(total) as total'),
                DB::raw('SUM(CASE WHEN estado = "pagada" THEN total ELSE 0 END) as pagado'),
                DB::raw('SUM(CASE WHEN estado = "pendiente" THEN total ELSE 0 END) as pendiente'),
                DB::raw('COUNT(*) as cantidad')
            )
            ->whereYear('fecha_emision', now()->year)
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();
        return response()->json($facturacion);
    }

    public function reporteCursos(Request $request)
    {
        $query = Curso::withCount(['grupos', 'inscripciones']);
        if ($request->has('fecha_desde') && $request->fecha_desde) {
            $query->where('fecha_inicio', '>=', $request->fecha_desde);
        }
        if ($request->has('fecha_hasta') && $request->fecha_hasta) {
            $query->where('fecha_inicio', '<=', $request->fecha_hasta);
        }
        $cursos = $query->orderBy('fecha_inicio', 'desc')->get()->map(function($curso) {
            return [
                'id' => $curso->id_curso,
                'nombre' => $curso->nombre,
                'tipo' => $curso->tipo,
                'estado' => $curso->estado,
                'fecha_inicio' => $curso->fecha_inicio,
                'fecha_fin' => $curso->fecha_fin,
                'precio' => $curso->precio,
                'cupo_maximo' => $curso->cupo_maximo,
                'cupo_actual' => $curso->cupo_actual,
                'grupos_count' => $curso->grupos_count,
                'inscripciones_count' => $curso->inscripciones_count,
            ];
        });
        $resumen = [
            'total_cursos' => $cursos->count(),
            'cursos_activos' => $cursos->where('estado', 'activo')->count(),
            'cursos_inactivos' => $cursos->where('estado', 'inactivo')->count(),
            'cursos_finalizados' => $cursos->where('estado', 'finalizado')->count(),
            'total_inscripciones' => $cursos->sum('inscripciones_count'),
            'total_grupos' => $cursos->sum('grupos_count'),
        ];
        return response()->json(['cursos' => $cursos, 'resumen' => $resumen]);
    }

    public function reporteFinanzas(Request $request)
    {
        $query = Factura::with(['tutor']);
        if ($request->has('fecha_desde') && $request->fecha_desde) {
            $query->where('fecha_emision', '>=', $request->fecha_desde);
        }
        if ($request->has('fecha_hasta') && $request->fecha_hasta) {
            $query->where('fecha_emision', '<=', $request->fecha_hasta);
        }
        $facturas = $query->orderBy('fecha_emision', 'desc')->get()->map(function($f) {
            return [
                'id' => $f->id_factura,
                'numero' => $f->numero,
                'cliente' => $f->tutor ? $f->tutor->nombre . ' ' . $f->tutor->apellido : 'N/A',
                'concepto' => $f->concepto,
                'subtotal' => $f->subtotal,
                'descuento' => $f->descuento,
                'impuesto' => $f->impuesto,
                'total' => $f->total,
                'estado' => $f->estado,
                'fecha_emision' => $f->fecha_emision,
                'fecha_vencimiento' => $f->fecha_vencimiento,
            ];
        });
        $resumen = [
            'total_facturas' => $facturas->count(),
            'total_facturado' => $facturas->sum('total'),
            'total_pagado' => $facturas->where('estado', 'pagada')->sum('total'),
            'total_pendiente' => $facturas->where('estado', 'pendiente')->sum('total'),
            'facturas_pagadas' => $facturas->where('estado', 'pagada')->count(),
            'facturas_pendientes' => $facturas->where('estado', 'pendiente')->count(),
        ];
        return response()->json(['facturas' => $facturas, 'resumen' => $resumen]);
    }

    public function reporteParticipantes(Request $request)
    {
        $query = Deportista::with(['categoria', 'tutores', 'inscripciones']);
        if ($request->has('fecha_desde') && $request->fecha_desde) {
            $query->where('created_at', '>=', $request->fecha_desde);
        }
        if ($request->has('fecha_hasta') && $request->fecha_hasta) {
            $query->where('created_at', '<=', $request->fecha_hasta);
        }
        $participantes = $query->orderBy('created_at', 'desc')->get()->map(function($d) {
            return [
                'id' => $d->id_deportista,
                'nombre' => $d->nombres . ' ' . $d->apellidos,
                'cedula' => $d->cedula,
                'edad' => $d->edad,
                'genero' => $d->genero,
                'categoria' => $d->categoria->nombre ?? null,
                'estado' => $d->estado,
                'tutor' => $d->tutores->first() ? $d->tutores->first()->nombre . ' ' . $d->tutores->first()->apellido : null,
                'inscripciones_activas' => $d->inscripciones->where('estado', 'activa')->count(),
                'fecha_registro' => $d->created_at ? $d->created_at->format('Y-m-d') : null,
            ];
        });
        $resumen = [
            'total_participantes' => $participantes->count(),
            'participantes_activos' => $participantes->where('estado', 'activo')->count(),
            'participantes_inactivos' => $participantes->where('estado', 'inactivo')->count(),
            'con_inscripciones' => $participantes->where('inscripciones_activas', '>', 0)->count(),
        ];
        return response()->json(['participantes' => $participantes, 'resumen' => $resumen]);
    }

    public function misDatos()
    {
        $user = auth()->user();
        $rolSlug = $user->rol ? $user->rol->slug : null;
        $datos = [
            'usuario' => [
                'nombre' => $user->nombre . ' ' . $user->apellido,
                'email' => $user->email,
                'rol' => $user->rol ? $user->rol->nombre : null,
            ]
        ];
        if ($rolSlug === 'tutor') {
            $participantes = Deportista::whereHas('tutores', function($q) use ($user) {
                $q->where('deportista_tutores.id_usuario', $user->id_usuario);
            })->get();
            $participantesIds = $participantes->pluck('id_deportista');
            $inscripciones = InscripcionCurso::with(['curso', 'grupo'])
                ->whereIn('id_deportista', $participantesIds)
                ->get();
            $facturas = Factura::where('id_tutor', $user->id_usuario)->get();
            $datos['participantes'] = [
                'total' => $participantes->count(),
                'activos' => $participantes->where('estado', 'activo')->count(),
                'lista' => $participantes->map(function($d) {
                    return [
                        'id' => $d->id_deportista,
                        'nombre' => $d->nombres . ' ' . $d->apellidos,
                        'edad' => $d->edad,
                    ];
                })
            ];
            $datos['inscripciones'] = [
                'total' => $inscripciones->count(),
                'activas' => $inscripciones->where('estado', 'activa')->count(),
                'completadas' => $inscripciones->where('estado', 'completada')->count(),
            ];
            $datos['facturas'] = [
                'total' => $facturas->count(),
                'pendientes' => $facturas->where('estado', 'pendiente')->count(),
                'monto_pendiente' => $facturas->where('estado', 'pendiente')->sum('total'),
                'pagadas' => $facturas->where('estado', 'pagada')->count(),
            ];
        }
        if ($rolSlug === 'instructor') {
            $grupos = GrupoCurso::where('id_instructor', $user->id_usuario)
                ->with(['curso', 'inscripciones'])
                ->get();
            $datos['grupos'] = [
                'total' => $grupos->count(),
                'activos' => $grupos->where('estado', 'activo')->count(),
                'lista' => $grupos->map(function($g) {
                    return [
                        'id' => $g->id_grupo,
                        'nombre' => $g->nombre,
                        'curso' => $g->curso ? $g->curso->nombre : null,
                        'participantes' => $g->inscripciones->where('estado', 'activa')->count(),
                        'cupo_maximo' => $g->cupo_maximo,
                    ];
                })
            ];
            $totalParticipantes = $grupos->sum(function($g) {
                return $g->inscripciones->where('estado', 'activa')->count();
            });
            $datos['participantes_total'] = $totalParticipantes;
        }
        return response()->json($datos);
    }
}
