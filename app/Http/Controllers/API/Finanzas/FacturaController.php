<?php
namespace App\Http\Controllers\API\Finanzas;

use App\Http\Controllers\Controller;
use App\Models\Factura;
use App\Models\DetalleFactura;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class FacturaController extends Controller
{
    public function index(Request $request)
    {
        $query = Factura::with(['deportista', 'tutor', 'inscripcion.curso', 'inscripcion.grupo', 'usuario', 'detalles', 'pagos']);

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('id_deportista')) {
            $query->where('id_deportista', $request->id_deportista);
        }

        if ($request->filled('id_tutor')) {
            $query->where('id_tutor', $request->id_tutor);
        }

        if ($request->filled('id_inscripcion')) {
            $query->where('id_inscripcion', $request->id_inscripcion);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_emision', '>=', $request->fecha_desde);
        }
        
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_emision', '<=', $request->fecha_hasta);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('numero_factura', 'like', "%{$search}%")
                  ->orWhere('concepto', 'like', "%{$search}%")
                  ->orWhereHas('deportista', function($q2) use ($search) {
                      $q2->where('nombres', 'like', "%{$search}%")
                         ->orWhere('apellidos', 'like', "%{$search}%");
                  })
                  ->orWhereHas('tutor', function($q2) use ($search) {
                      $q2->where('nombre', 'like', "%{$search}%")
                         ->orWhere('apellido', 'like', "%{$search}%");
                  });
            });
        }

        $facturas = $query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 15);
        return response()->json($facturas);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_deportista' => 'required|exists:deportistas,id_deportista',
            'id_tutor' => 'nullable|exists:usuarios,id_usuario',
            'id_inscripcion' => 'nullable|exists:inscripcion_cursos,id_inscripcion',
            'concepto' => 'required|string|max:200',
            'fecha_emision' => 'required|date',
            'fecha_vencimiento' => 'nullable|date|after:fecha_emision',
            'descuento' => 'nullable|numeric|min:0',
            'impuesto' => 'nullable|numeric|min:0',
            'metodo_pago' => 'nullable|in:efectivo,tarjeta,transferencia,cheque,otro',
            'observaciones' => 'nullable|string',
            'detalles' => 'required|array|min:1',
            'detalles.*.concepto' => 'required|string|max:100',
            'detalles.*.descripcion' => 'nullable|string',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
            'detalles.*.descuento' => 'nullable|numeric|min:0'
        ]);

        DB::beginTransaction();
        try {
            $ultimaFactura = Factura::latest('id_factura')->first();
            $numeroFactura = 'FAC-' . str_pad(($ultimaFactura ? $ultimaFactura->id_factura + 1 : 1), 8, '0', STR_PAD_LEFT);

            $factura = Factura::create([
                'id_deportista' => $request->id_deportista,
                'id_tutor' => $request->id_tutor,
                'id_inscripcion' => $request->id_inscripcion,
                'usuario_id' => Auth::id(),
                'numero_factura' => $numeroFactura,
                'concepto' => $request->concepto,
                'fecha_emision' => $request->fecha_emision,
                'fecha_vencimiento' => $request->fecha_vencimiento,
                'descuento' => $request->descuento ?? 0,
                'impuesto' => $request->impuesto ?? 0,
                'subtotal' => 0,
                'total' => 0,
                'estado' => 'pendiente',
                'metodo_pago' => $request->metodo_pago,
                'observaciones' => $request->observaciones,
                'created_by' => Auth::id()
            ]);

            $subtotal = 0;
            foreach ($request->detalles as $detalle) {
                $cantidad = $detalle['cantidad'];
                $precioUnitario = $detalle['precio_unitario'];
                $descuentoDetalle = $detalle['descuento'] ?? 0;
                
                $subtotalDetalle = $cantidad * $precioUnitario;
                $montoDetalle = $subtotalDetalle - $descuentoDetalle;
                
                DetalleFactura::create([
                    'id_factura' => $factura->id_factura,
                    'concepto' => $detalle['concepto'],
                    'descripcion' => $detalle['descripcion'] ?? null,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precioUnitario,
                    'subtotal' => $subtotalDetalle,
                    'descuento' => $descuentoDetalle,
                    'monto' => $montoDetalle
                ]);

                $subtotal += $montoDetalle;
            }

            $total = $subtotal - ($request->descuento ?? 0) + ($request->impuesto ?? 0);
            $factura->update([
                'subtotal' => $subtotal,
                'total' => $total
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Factura creada exitosamente',
                'data' => $factura->load(['deportista', 'tutor', 'inscripcion', 'detalles'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al crear factura',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $factura = Factura::with([
            'deportista', 
            'tutor', 
            'inscripcion.curso', 
            'inscripcion.grupo',
            'usuario', 
            'detalles', 
            'pagos'
        ])->findOrFail($id);
        
        $data = $factura->toArray();
        $data['saldo_pendiente'] = $factura->saldo_pendiente;
        $data['total_pagado'] = $factura->total_pagado;
        
        return response()->json($data);
    }

    public function update(Request $request, $id)
    {
        $factura = Factura::findOrFail($id);

        if ($factura->estado === 'pagada') {
            return response()->json([
                'message' => 'No se puede modificar una factura pagada'
            ], 400);
        }

        $request->validate([
            'id_tutor' => 'nullable|exists:usuarios,id_usuario',
            'fecha_vencimiento' => 'nullable|date',
            'estado' => 'sometimes|in:pendiente,pagada,vencida,cancelada',
            'observaciones' => 'nullable|string'
        ]);

        $factura->update([
            ...$request->only(['id_tutor', 'fecha_vencimiento', 'estado', 'observaciones']),
            'updated_by' => Auth::id()
        ]);

        return response()->json([
            'message' => 'Factura actualizada exitosamente',
            'data' => $factura->load(['deportista', 'tutor', 'inscripcion'])
        ]);
    }

    public function destroy($id)
    {
        $factura = Factura::findOrFail($id);

        if ($factura->estado === 'pagada') {
            return response()->json([
                'message' => 'No se puede eliminar una factura pagada'
            ], 400);
        }

        $factura->update(['deleted_by' => Auth::id()]);
        $factura->delete();

        return response()->json([
            'message' => 'Factura eliminada exitosamente'
        ]);
    }

    public function registrarPago(Request $request, $id)
    {
        $request->validate([
            'monto' => 'required|numeric|min:0.01',
            'fecha_pago' => 'required|date',
            'metodo_pago' => 'required|in:efectivo,tarjeta,transferencia,cheque,otro',
            'referencia' => 'nullable|string|max:100',
            'comprobante' => 'nullable|file|max:5120',
            'observaciones' => 'nullable|string'
        ]);

        $factura = Factura::findOrFail($id);

        if ($factura->estado === 'pagada') {
            return response()->json([
                'message' => 'Esta factura ya esta completamente pagada'
            ], 400);
        }

        if ($factura->estado === 'cancelada') {
            return response()->json([
                'message' => 'No se puede registrar pago en una factura cancelada'
            ], 400);
        }

        $saldoPendiente = $factura->saldo_pendiente;
        if ($request->monto > $saldoPendiente) {
            return response()->json([
                'message' => "El monto excede el saldo pendiente (${saldoPendiente})"
            ], 400);
        }

        DB::beginTransaction();
        try {
            $ultimoPago = Pago::latest('id_pago')->first();
            $numeroPago = 'PAG-' . str_pad(($ultimoPago ? $ultimoPago->id_pago + 1 : 1), 8, '0', STR_PAD_LEFT);

            $pagoData = [
                'id_factura' => $factura->id_factura,
                'numero_pago' => $numeroPago,
                'monto' => $request->monto,
                'fecha_pago' => $request->fecha_pago,
                'metodo_pago' => $request->metodo_pago,
                'referencia' => $request->referencia,
                'observaciones' => $request->observaciones,
                'estado' => 'verificado',
                'created_by' => Auth::id()
            ];

            if ($request->hasFile('comprobante')) {
                $pagoData['comprobante'] = $request->file('comprobante')->store('pagos/comprobantes', 'public');
            }

            $pago = Pago::create($pagoData);

            // Actualizar estado de factura si esta completamente pagada
            $factura->refresh();
            if ($factura->saldo_pendiente <= 0) {
                $factura->update([
                    'estado' => 'pagada',
                    'metodo_pago' => $request->metodo_pago
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Pago registrado exitosamente',
                'data' => $pago,
                'factura' => $factura->load('pagos'),
                'saldo_pendiente' => $factura->fresh()->saldo_pendiente
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al registrar pago',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function facturasPorTutor($idTutor)
    {
        $facturas = Factura::with(['deportista', 'inscripcion.curso', 'pagos'])
            ->where('id_tutor', $idTutor)
            ->orderBy('created_at', 'desc')
            ->get();

        $resumen = [
            'total_facturas' => $facturas->count(),
            'total_pendiente' => $facturas->where('estado', 'pendiente')->sum('total'),
            'total_pagado' => $facturas->where('estado', 'pagada')->sum('total'),
            'facturas' => $facturas
        ];

        return response()->json($resumen);
    }

    public function reporteFacturacion(Request $request)
    {
        $request->validate([
            'fecha_desde' => 'required|date',
            'fecha_hasta' => 'required|date|after_or_equal:fecha_desde'
        ]);

        $estadisticas = Factura::whereBetween('fecha_emision', [$request->fecha_desde, $request->fecha_hasta])
            ->selectRaw('
                estado,
                COUNT(*) as cantidad,
                SUM(total) as total_monto,
                SUM(CASE WHEN estado = "pagada" THEN total ELSE 0 END) as total_cobrado
            ')
            ->groupBy('estado')
            ->get();

        $totales = [
            'total_facturado' => $estadisticas->sum('total_monto'),
            'total_cobrado' => $estadisticas->where('estado', 'pagada')->sum('total_monto'),
            'total_pendiente' => $estadisticas->where('estado', 'pendiente')->sum('total_monto'),
            'por_estado' => $estadisticas
        ];

        return response()->json($totales);
    }

    /**
     * Obtener las facturas del tutor autenticado
     * Solo para rol TUTOR
     */
    public function misFacturas(Request $request)
    {
        $user = Auth::user();
        
        // El usuario autenticado ES el tutor (ya no hay tabla separada)
        $query = Factura::with(['deportista', 'inscripcion.curso', 'inscripcion.grupo', 'pagos'])
            ->where('id_tutor', $user->id_usuario);

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $facturas = $query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 15);

        return response()->json($facturas);
    }

    /**
     * Exportar factura a PDF (HTML para imprimir)
     */
    public function exportarPdf($id)
    {
        $factura = Factura::with([
            'deportista', 
            'tutor', 
            'inscripcion.curso', 
            'inscripcion.grupo',
            'detalles', 
            'pagos'
        ])->findOrFail($id);

        $html = view('facturas.pdf', compact('factura'))->render();
        
        return response($html)
            ->header('Content-Type', 'text/html');
    }

    /**
     * Obtener datos de factura para generar PDF en frontend
     */
    public function datosParaPdf($id)
    {
        $factura = Factura::with([
            'deportista', 
            'tutor', 
            'inscripcion.curso', 
            'inscripcion.grupo',
            'detalles', 
            'pagos'
        ])->findOrFail($id);

        return response()->json([
            'factura' => $factura,
            'empresa' => [
                'nombre' => 'Cursos Vacacionales',
                'direccion' => 'Ecuador',
                'telefono' => '',
                'email' => '',
                'ruc' => '',
            ]
        ]);
    }
}
