<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Venta;
use App\Models\Pago;
use Inertia\Inertia;
use GuzzleHttp\Client;

class PagoFacilController extends Controller
{
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
            Log::info('Inicio del método generarQR', ['request' => $request->all()]);

            $request->validate([
                'venta_id' => 'required|exists:ventas,id',
                'metodo_pago' => 'required|in:qr,tigo_money'
            ]);

            $venta = Venta::with(['detalles.producto', 'pedido.usuario'])->findOrFail($request->venta_id);
            Log::info('Venta encontrada', ['venta' => $venta]);

            // Obtener token de autenticación
            $tokenResponse = $this->obtenerToken();
            Log::info('Token obtenido', ['tokenResponse' => $tokenResponse]);

            if (!isset($tokenResponse["values"])) {
                Log::error('No se pudo obtener un token válido');
                return response()->json(['success' => false, 'message' => 'No se pudo obtener un token válido'], 500);
            }

            $accessToken = $tokenResponse["values"];

            // Preparar datos del pedido
            $pedidoDetalle = $this->formatearDetallesPedido($venta);
            $nroPago = "venta-" . $venta->id . "-" . time();

            // Cuerpo de la solicitud para QR
            $body = [
                "tcCommerceID" => config('pagofacil.commerce_id'),
                "tcNroPago" => $nroPago,
                "tcNombreUsuario" => $venta->pedido->usuario->name,
                "tnCiNit" => (int)($request->ci_nit ?? 0),
                "tnTelefono" => (int)($request->telefono ?? 0),
                "tcCorreo" => $venta->pedido->usuario->email,
                "tnMontoClienteEmpresa" => (float)$venta->total,
                "tnMoneda" => 2,
                "tcUrlCallBack" => config('pagofacil.callback_url'),
                "tcUrlReturn" => config('pagofacil.return_url'),
                "taPedidoDetalle" => $pedidoDetalle,
            ];

            Log::info('Cuerpo de la solicitud generado', ['body' => $body]);

            // Encabezados
            $headers = [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken
            ];

            // Cliente HTTP
            $client = new Client();

            // Realizar la solicitud
            $url = config('pagofacil.base_url') . '/api/servicio/pagoqr';
            Log::info('Enviando solicitud a PagoFácil', ['url' => $url]);

            $response = $client->post($url, [
                'headers' => $headers,
                'json' => $body
            ]);

            $responseContent = $response->getBody()->getContents();
            Log::info('Contenido crudo de la respuesta', ['response' => $responseContent]);

            // Decodificar JSON principal
            $result = json_decode($responseContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Error al decodificar JSON principal', [
                    'error_message' => json_last_error_msg(),
                    'response_content' => $responseContent
                ]);
                return response()->json(['success' => false, 'message' => 'Error al procesar la respuesta del servicio'], 500);
            }

            // Verificar que `values` exista en la respuesta
            if (!isset($result['values'])) {
                Log::error('El campo values no está presente en la respuesta', ['result' => $result]);
                return response()->json(['success' => false, 'message' => 'Respuesta inesperada del servicio'], 500);
            }

            // Dividir el campo `values`
            $valuesParts = explode(";", $result['values']);

            if (count($valuesParts) < 2) {
                Log::error('El campo values no contiene datos esperados', ['values' => $result['values']]);
                return response()->json(['success' => false, 'message' => 'Respuesta inesperada en el campo values'], 500);
            }

            // Extraer número de transacción
            $nroTransaccion = $valuesParts[0];

            // Extraer y decodificar el QR
            $jsonEscaped = $valuesParts[1];
            $qrData = json_decode($jsonEscaped, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Error al decodificar JSON del campo values', [
                    'error_message' => json_last_error_msg(),
                    'json_escaped' => $jsonEscaped
                ]);
                return response()->json(['success' => false, 'message' => 'Error al procesar los datos del QR'], 500);
            }

            // Extraer el QR imagen
            $qrImage = $qrData['qrImage'] ?? null;
            $qrImageBase64 = "data:image/png;base64," . $qrImage;

            if (!$qrImage) {
                Log::error('QR imagen no encontrada en los datos del QR', ['qrData' => $qrData]);
                return response()->json(['success' => false, 'message' => 'No se encontró el QR'], 500);
            }

            // Crear registro de pago pendiente
            $pago = Pago::create([
                'venta_id' => $venta->id,
                'monto' => $venta->total,
                'fecha' => now(),
                'metodo_pago' => 'PAGO_FACIL',
                'estado' => 'pendiente',
                'referencia_externa' => $nroPago,
                'transaction_id' => $nroTransaccion,
                'datos_pago' => json_encode($result)
            ]);

            Log::info('QR y número de transacción generados correctamente', [
                'qrImage' => substr($qrImageBase64, 0, 50) . '...',
                'nroTransaccion' => $nroTransaccion,
                'pago_id' => $pago->id
            ]);

            return response()->json([
                'success' => true,
                'qr_image' => $qrImageBase64,
                'transaction_id' => $nroTransaccion,
                'nro_pago' => $nroPago
            ]);

        } catch (\Throwable $th) {
            Log::error('Error en generarQR', [
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile()
            ]);
            return response()->json(['success' => false, 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * Consultar estado del pago
     */
    public function consultarEstado(Request $request)
    {
        try {
            $transactionId = $request->input('transaction_id');
            
            $client = new Client();

            $response = $client->post(config('pagofacil.base_url') . '/api/servicio/consultartransaccion', [
                'headers' => [
                    'Accept' => 'application/json'
                ],
                'json' => [
                    "TransaccionDePago" => $transactionId
                ]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            
            return response()->json([
                'success' => true,
                'estado' => $result['values']['messageEstado'] ?? 0,
                'data' => $result['values'] ?? []
            ]);

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
            Log::info('Callback recibido de Pago Fácil', ['data' => $request->all()]);

            // Procesar la notificación
            $nroPago = $request->input('nro_pago');
            $estado = $request->input('estado');
            $transactionId = $request->input('transaction_id');

            if ($nroPago && $estado == 'completado') {
                $pago = Pago::where('referencia_externa', $nroPago)->first();
                
                if ($pago) {
                    $pago->update([
                        'estado' => 'completado',
                        'fecha_pago' => now(),
                        'datos_pago' => json_encode($request->all())
                    ]);

                    Log::info('Pago actualizado como completado', ['pago_id' => $pago->id]);
                }
            }

            return response()->json([
                'error' => 0,
                'status' => 1,
                'message' => "Pago procesado correctamente.",
                'values' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Error en callback: ' . $e->getMessage());
            return response()->json([
                'error' => 1,
                'status' => 1,
                'messageSistema' => "[TRY/CATCH] " . $e->getMessage(),
                'message' => "No se pudo procesar el pago, por favor intente de nuevo.",
                'values' => false
            ]);
        }
    }

    /**
     * Página de retorno después del pago
     */
    public function return(Request $request)
    {
        $status = $request->query('status', 'pending');
        $nroPago = $request->query('nro_pago');
        $message = '';

        if ($status === 'success') {
            $message = 'Pago completado exitosamente';
        } elseif ($status === 'error') {
            $message = 'Hubo un error con el pago';
        } else {
            $message = 'Pago pendiente de confirmación';
        }

        return Inertia::render('PagoFacil/Return', [
            'status' => $status,
            'message' => $message,
            'nro_pago' => $nroPago
        ]);
    }

    /**
     * Obtener token de autenticación de Pago Fácil
     */
    private function obtenerToken()
    {
        try {
            $client = new Client();

            $response = $client->post(config('pagofacil.base_url') . '/api/servicio/login', [
                'headers' => [
                    'Accept' => 'application/json'
                ],
                'json' => [
                    'TokenService' => config('pagofacil.token_service'),
                    'TokenSecret' => config('pagofacil.token_secret')
                ],
                'timeout' => config('pagofacil.timeout', 30)
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            if (config('pagofacil.enable_logs')) {
                Log::info('Token obtenido de Pago Fácil', ['response' => $result]);
            }

            return $result;
        } catch (\Exception $e) {
            if (config('pagofacil.enable_logs')) {
                Log::error('Error al obtener token de Pago Fácil', [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
            }
            throw new \Exception("Error al obtener el token: " . $e->getMessage());
        }
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
}