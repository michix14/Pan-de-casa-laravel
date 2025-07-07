<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Venta;
use App\Models\Pago;
use Inertia\Inertia;

class PagoFacilController extends Controller
{
    private $baseUrl;
    private $tokenService;
    private $tokenSecret;

    public function __construct()
    {
        $this->baseUrl = config('pagofacil.base_url');
        $this->tokenService = config('pagofacil.token_service');
        $this->tokenSecret = config('pagofacil.token_secret');
    }

    /**
     * Mostrar la página de pago
     */
    public function index(Request $request)
    {
        $ventaId = $request->query('venta_id');
        
        if (!$ventaId) {
            return redirect()->route('ventas.index')->with('error', 'ID de venta requerido.');
        }

        $venta = Venta::with(['detalles.producto', 'pedido.usuario'])->findOrFail($ventaId);

        return Inertia::render('PagoFacil/Index', [
            'venta' => $venta
        ]);
    }

    /**
     * Generar QR para pago
     */
    public function generarQR(Request $request)
    {
        try {
            $request->validate([
                'venta_id' => 'required|exists:ventas,id',
                'metodo_pago' => 'required|in:qr,tigo_money'
            ]);

            $venta = Venta::findOrFail($request->venta_id);

            // Generar datos de la transacción
            $transactionData = [
                'tcCommerceID' => config('pagofacil.commerce_id'),
                'tnMoneda' => 2, // BOB (Bolivianos)
                'tnTelefono' => $request->telefono ?? '',
                'tcNombreUsuario' => auth()->user()->name,
                'tnCiNit' => $request->ci_nit ?? '',
                'tcNroPago' => 'VENTA-' . $venta->id . '-' . time(),
                'tnMontoClienteEmpresa' => $venta->total,
                'tcCorreo' => auth()->user()->email,
                'tcUrlCallBack' => route('pagofacil.callback'),
                'tcUrlReturn' => route('pagofacil.return'),
                'taPedidoDetalle' => $this->formatearDetallesPedido($venta)
            ];

            if ($request->metodo_pago === 'qr') {
                return $this->generarQRSimple($transactionData);
            } else {
                return $this->generarTigoMoney($transactionData);
            }

        } catch (\Exception $e) {
            Log::error('Error al generar QR de Pago Fácil: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la solicitud de pago'
            ], 500);
        }
    }

    /**
     * Generar QR simple
     */
    private function generarQRSimple($data)
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json'
        ])->post($this->baseUrl . '/api/servicio/generarqrsimple', [
            'tcParametros' => json_encode($data),
            'tcTokenService' => $this->tokenService
        ]);

        if ($response->successful()) {
            $responseData = $response->json();
            
            if (isset($responseData['values']) && $responseData['values']['tnResultadoOperacion'] == 1) {
                // Guardar registro del pago pendiente
                $this->crearPagoPendiente($data['tcNroPago'], $data['tnMontoClienteEmpresa']);
                
                return response()->json([
                    'success' => true,
                    'qr_image' => $responseData['values']['tcQrImage'],
                    'transaction_id' => $responseData['values']['tnTransaccion'],
                    'nro_pago' => $data['tcNroPago']
                ]);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Error al generar el código QR'
        ], 400);
    }

    /**
     * Generar pago con Tigo Money
     */
    private function generarTigoMoney($data)
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json'
        ])->post($this->baseUrl . '/api/servicio/realizarpagotigomoneyempresa', [
            'tcParametros' => json_encode($data),
            'tcTokenService' => $this->tokenService
        ]);

        if ($response->successful()) {
            $responseData = $response->json();
            
            if (isset($responseData['values']) && $responseData['values']['tnResultadoOperacion'] == 1) {
                // Guardar registro del pago pendiente
                $this->crearPagoPendiente($data['tcNroPago'], $data['tnMontoClienteEmpresa']);
                
                return response()->json([
                    'success' => true,
                    'transaction_id' => $responseData['values']['tnTransaccion'],
                    'nro_pago' => $data['tcNroPago'],
                    'message' => 'Revisa tu teléfono para confirmar el pago con Tigo Money'
                ]);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Error al procesar el pago con Tigo Money'
        ], 400);
    }

    /**
     * Consultar estado del pago
     */
    public function consultarEstado(Request $request)
    {
        try {
            $transactionId = $request->input('transaction_id');
            
            $response = Http::withHeaders([
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . '/api/servicio/consultartransaccion', [
                'tcParametros' => json_encode([
                    'tnTransaccion' => $transactionId
                ]),
                'tcTokenService' => $this->tokenService
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                
                return response()->json([
                    'success' => true,
                    'estado' => $responseData['values']['tnEstado'] ?? 0,
                    'data' => $responseData['values'] ?? []
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error al consultar el estado del pago'
            ], 400);

        } catch (\Exception $e) {
            Log::error('Error al consultar estado de pago: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Callback para notificaciones de Pago Fácil
     */
    public function callback(Request $request)
    {
        try {
            Log::info('Callback de Pago Fácil recibido', $request->all());

            $messageId = $request->input('MessageId');
            $message = $request->input('Message');

            if ($messageId && $message) {
                // Verificar la firma del mensaje
                if ($this->verificarFirma($messageId, $message)) {
                    $this->procesarPagoExitoso($message);
                }
            }

            return response('OK', 200);

        } catch (\Exception $e) {
            Log::error('Error en callback de Pago Fácil: ' . $e->getMessage());
            return response('Error', 500);
        }
    }

    /**
     * Página de retorno después del pago
     */
    public function return(Request $request)
    {
        $status = $request->query('status', 'pending');
        $nroPago = $request->query('nro_pago');
        
        return Inertia::render('PagoFacil/Return', [
            'status' => $status,
            'nro_pago' => $nroPago
        ]);
    }

    /**
     * Formatear detalles del pedido para Pago Fácil
     */
    private function formatearDetallesPedido($venta)
    {
        $detalles = [];
        
        foreach ($venta->detalles as $detalle) {
            $detalles[] = [
                'Serial' => $detalle->id,
                'Producto' => $detalle->producto->nombre,
                'Cantidad' => $detalle->cantidad,
                'Precio' => $detalle->precio_unitario,
                'Descuento' => 0,
                'Total' => $detalle->cantidad * $detalle->precio_unitario
            ];
        }

        return $detalles;
    }

    /**
     * Crear registro de pago pendiente
     */
    private function crearPagoPendiente($nroPago, $monto)
    {
        $ventaId = explode('-', $nroPago)[1];
        
        Pago::create([
            'venta_id' => $ventaId,
            'metodo_pago' => 'pago_facil',
            'monto' => $monto,
            'estado' => 'pendiente',
            'referencia_externa' => $nroPago,
            'datos_pago' => json_encode(['nro_pago' => $nroPago])
        ]);
    }

    /**
     * Verificar firma del mensaje del callback
     */
    private function verificarFirma($messageId, $message)
    {
        $expectedSignature = hash_hmac('sha256', $messageId . $message, $this->tokenSecret);
        $receivedSignature = hash_hmac('sha256', $messageId . $message, $this->tokenSecret);
        
        return hash_equals($expectedSignature, $receivedSignature);
    }

    /**
     * Procesar pago exitoso
     */
    private function procesarPagoExitoso($message)
    {
        $data = json_decode($message, true);
        
        if (isset($data['PedidoID'])) {
            $nroPago = $data['PedidoID'];
            $ventaId = explode('-', $nroPago)[1];
            
            // Actualizar el pago
            $pago = Pago::where('referencia_externa', $nroPago)->first();
            if ($pago) {
                $pago->update([
                    'estado' => 'completado',
                    'fecha_pago' => now(),
                    'datos_pago' => json_encode($data)
                ]);

                // Actualizar la venta
                $venta = Venta::find($ventaId);
                if ($venta && $venta->pedido) {
                    $venta->pedido->update(['estado' => 'pagado']);
                }
            }
        }
    }
}
