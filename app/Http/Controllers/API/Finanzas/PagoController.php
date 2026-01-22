<?php
namespace App\Http\Controllers\API\Finanzas;
use App\Http\Controllers\Controller;

use App\Models\Pago;
use Illuminate\Http\Request;

class PagoController extends Controller
{
    public function index(Request $request)
    {
        $query = Pago::with('factura');
        
        if ($request->has('estado') && $request->estado !== 'all') {
            $query->where('estado', $request->estado);
        }

        if ($request->has('id_factura') && $request->id_factura !== 'all') {
            $query->where('id_factura', $request->id_factura);
        }

        if ($request->has('metodo_pago') && $request->metodo_pago !== 'all') {
            $query->where('metodo_pago', $request->metodo_pago);
        }

        if ($request->has('search') && $request->search !== '') {
            $query->where(function($q) use ($request) {
                $q->where('numero_pago', 'like', '%' . $request->search . '%')
                  ->orWhere('referencia', 'like', '%' . $request->search . '%');
            });
        }

        $pagos = $query->orderBy('fecha_pago', 'desc')->paginate(15);
        
        return response()->json($pagos);
    }

    public function show($id)
    {
        $pago = Pago::with('factura')->findOrFail($id);
        return response()->json($pago);
    }

    public function update(Request $request, $id)
    {
        $pago = Pago::findOrFail($id);

        $request->validate([
            'estado' => 'sometimes|in:verificado,pendiente,rechazado',
            'observaciones' => 'nullable|string'
        ]);

        $pago->update($request->all());

        return response()->json([
            'message' => 'Pago actualizado exitosamente',
            'data' => $pago
        ]);
    }

    public function verificar($id)
    {
        $pago = Pago::findOrFail($id);
        
        $pago->update(['estado' => 'verificado']);

        // Actualizar estado de factura si está completamente pagada
        $factura = $pago->factura;
        if ($factura && $factura->saldo_pendiente <= 0) {
            $factura->update(['estado' => 'pagada']);
        }

        return response()->json([
            'message' => 'Pago verificado exitosamente',
            'data' => $pago->fresh()
        ]);
    }

    public function rechazar(Request $request, $id)
    {
        $request->validate([
            'observaciones' => 'required|string'
        ]);

        $pago = Pago::findOrFail($id);
        $pago->update([
            'estado' => 'rechazado',
            'observaciones' => $request->observaciones
        ]);

        return response()->json([
            'message' => 'Pago rechazado',
            'data' => $pago
        ]);
    }

    public function destroy($id)
    {
        $pago = Pago::findOrFail($id);

        if ($pago->estado === 'verificado') {
            return response()->json([
                'message' => 'No se puede eliminar un pago verificado'
            ], 400);
        }

        $pago->delete();

        return response()->json([
            'message' => 'Pago eliminado exitosamente'
        ]);
    }

    /**
     * Obtener los pagos del tutor autenticado
     */
    public function misPagos(Request $request)
    {
        $user = auth()->user();

        // Obtener facturas del tutor (el usuario autenticado ES el tutor)
        $facturaIds = \App\Models\Factura::where('id_tutor', $user->id_usuario)
            ->pluck('id_factura');

        $query = Pago::with(['factura.deportista', 'factura.inscripcion.curso'])
            ->whereIn('id_factura', $facturaIds);

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $pagos = $query->orderBy('fecha_pago', 'desc')->paginate($request->per_page ?? 15);

        return response()->json($pagos);
    }

    /**
     * Realizar un pago (para tutores)
     */
    public function realizarPago(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'id_factura' => 'required|exists:facturas,id_factura',
            'monto' => 'required|numeric|min:0.01',
            'metodo_pago' => 'required|in:efectivo,tarjeta,transferencia,cheque,otro',
            'referencia' => 'nullable|string|max:100',
            'comprobante' => 'nullable|image|max:5120', // 5MB max
            'observaciones' => 'nullable|string'
        ]);

        $factura = \App\Models\Factura::findOrFail($request->id_factura);

        // Verificar que la factura pertenece al tutor (usuario autenticado)
        if ($factura->id_tutor !== $user->id_usuario) {
            return response()->json([
                'message' => 'No tienes permiso para pagar esta factura'
            ], 403);
        }

        if ($factura->estado === 'pagada') {
            return response()->json([
                'message' => 'Esta factura ya está pagada'
            ], 400);
        }

        $saldoPendiente = $factura->saldo_pendiente;
        if ($request->monto > $saldoPendiente) {
            return response()->json([
                'message' => "El monto excede el saldo pendiente (\${$saldoPendiente})"
            ], 400);
        }

        $ultimoPago = Pago::latest('id_pago')->first();
        $numeroPago = 'PAG-' . str_pad(($ultimoPago ? $ultimoPago->id_pago + 1 : 1), 8, '0', STR_PAD_LEFT);

        $comprobanteUrl = null;
        if ($request->hasFile('comprobante')) {
            try {
                // Subir a Cloudinary
                $uploadedFile = $request->file('comprobante');
                $cloudinaryResponse = cloudinary()->upload($uploadedFile->getRealPath(), [
                    'folder' => 'comprobantes_pago',
                    'resource_type' => 'image'
                ]);
                $comprobanteUrl = $cloudinaryResponse->getSecurePath();
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Error al subir el comprobante',
                    'error' => $e->getMessage()
                ], 500);
            }
        }

        $pago = Pago::create([
            'id_factura' => $factura->id_factura,
            'numero_pago' => $numeroPago,
            'monto' => $request->monto,
            'fecha_pago' => now(),
            'metodo_pago' => $request->metodo_pago,
            'referencia' => $request->referencia,
            'comprobante' => $comprobanteUrl,
            'observaciones' => $request->observaciones,
            'estado' => 'pendiente', // Pendiente de verificación por admin
            'created_by' => $user->id_usuario
        ]);

        return response()->json([
            'message' => 'Pago registrado exitosamente. Pendiente de verificación.',
            'data' => $pago->load('factura')
        ], 201);
    }
}
