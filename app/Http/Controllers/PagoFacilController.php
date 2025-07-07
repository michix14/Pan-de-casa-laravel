<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Venta;
use App\Models\Pago;
use App\Models\Pedido;
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
            
            if (!$transactionId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction ID es requerido'
                ], 400);
            }

            // Obtener token de autenticación
            $tokenResponse = $this->obtenerToken();
            $accessToken = $tokenResponse['values'] ?? null;

            if (!$accessToken) {
                Log::error('No se pudo obtener el token de PagoFácil para consultar estado');
                return response()->json([
                    'success' => false,
                    'message' => 'Error de autenticación con PagoFácil'
                ], 500);
            }

            $client = new Client();

            // Realizar consulta del estado
            $response = $client->post(config('pagofacil.base_url') . '/api/servicio/consultartransaccion', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $accessToken
                ],
                'json' => [
                    'TransaccionDePago' => $transactionId
                ]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            
            Log::info('Respuesta de consulta de estado', [
                'transaction_id' => $transactionId,
                'response' => $result
            ]);

            // Verificar la estructura de la respuesta
            if (!isset($result['values'])) {
                Log::error('Respuesta inesperada de PagoFácil', ['result' => $result]);
                return response()->json([
                    'success' => false,
                    'message' => 'Respuesta inesperada del servicio'
                ], 500);
            }

            $estado = $result['values']['messageEstado'] ?? 0;
            $estadoTexto = $result['values']['messageEstadoDescription'] ?? '';
            $horaPago = $result['values']['HoraPago'] ?? null;
            $fechaPago = $result['values']['FechaPago'] ?? null;

            // Buscar el pago en nuestra base de datos
            $pago = Pago::where('transaction_id', $transactionId)->first();

            // Determinar si el pago está completado basado en múltiples criterios
            $tieneHoraYFecha = ($horaPago !== null && $fechaPago !== null);
            $estadoProcesado = $estadoTexto && (
                str_contains($estadoTexto, 'PROCESADO') || 
                str_contains($estadoTexto, 'COMPLETADO - PROCESADO')
            );
            $pagoCompletado = $tieneHoraYFecha || $estadoProcesado;

            if ($pago && $pagoCompletado) { 
                // Pago completado (tiene hora y fecha de pago, o estado indica procesado)
                $pago->update([
                    'estado' => 'completado',
                    'fecha_pago' => now(),
                    'datos_pago' => json_encode($result['values'])
                ]);

                // Actualizar el estado del pedido a COMPLETADO
                $this->actualizarEstadoPedido($pago);

                Log::info('Pago actualizado como completado', [
                    'pago_id' => $pago->id,
                    'transaction_id' => $transactionId,
                    'tiene_hora_y_fecha' => $tieneHoraYFecha,
                    'estado_procesado' => $estadoProcesado,
                    'hora_pago' => $horaPago,
                    'fecha_pago' => $fechaPago,
                    'estado_texto' => $estadoTexto
                ]);
            } elseif ($pago && $estado == 3) { 
                // Estado 3 = Pago rechazado
                $pago->update([
                    'estado' => 'rechazado',
                    'datos_pago' => json_encode($result['values'])
                ]);

                Log::info('Pago marcado como rechazado', [
                    'pago_id' => $pago->id,
                    'transaction_id' => $transactionId
                ]);
            } elseif ($pago) {
                // Actualizar los datos aunque esté pendiente
                $pago->update([
                    'datos_pago' => json_encode($result['values'])
                ]);
                
                Log::info('Pago aún pendiente, datos actualizados', [
                    'pago_id' => $pago->id,
                    'transaction_id' => $transactionId,
                    'hora_pago' => $horaPago,
                    'fecha_pago' => $fechaPago,
                    'estado_actual' => $estado
                ]);
            }
            
            return response()->json([
                'success' => true,
                'estado' => $estado,
                'estado_texto' => $estadoTexto,
                'pago_completado' => $pagoCompletado,
                'hora_pago' => $horaPago,
                'fecha_pago' => $fechaPago,
                'data' => $result['values'] ?? []
            ]);

        } catch (\Exception $e) {
            Log::error('Error al consultar estado de pago', [
                'transaction_id' => $request->input('transaction_id'),
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Callback para notificaciones de Pago Fácil
     * Recibe notificaciones cuando se completa un pago
     * 
     * Estructura de datos recibidos de PagoFácil:
     * {
     *   "PedidoID": "El Id de identificación del pedido (ej: venta-9-1751920294)",
     *   "Fecha": "Fecha de realización del pago",
     *   "Hora": "Hora del pago",
     *   "MetodoPago": "Medio por el que se realizo el pago",
     *   "Estado": "Estado del pago"
     * }
     */
    public function callback(Request $request)
    {
        try {
            Log::info('Callback recibido de Pago Fácil', ['data' => $request->all()]);

            // Validar que se recibieron todos los datos necesarios
            $pedidoId = $request->input('PedidoID'); // Formato: "venta-9-1751920294"
            $fecha = $request->input('Fecha');
            $hora = $request->input('Hora');
            $metodoPago = $request->input('MetodoPago');
            $estado = $request->input('Estado');

            if (!$pedidoId) {
                Log::error('Callback sin PedidoID', ['data' => $request->all()]);
                return response()->json([
                    'error' => 1,
                    'estatus' => 0,
                    'message' => "PedidoID es requerido",
                    'values' => false
                ]);
            }

            Log::info('Buscando pago con referencia externa', ['pedido_id' => $pedidoId]);

            // Buscar el pago en nuestra base de datos usando la referencia externa
            $pago = Pago::where('referencia_externa', $pedidoId)->first();

            if (!$pago) {
                // Buscar todos los pagos para debugging
                $todosPagos = Pago::select('id', 'referencia_externa', 'venta_id', 'estado')->get();
                
                Log::error('Pago no encontrado en base de datos', [
                    'pedido_id_buscado' => $pedidoId,
                    'callback_data' => $request->all(),
                    'pagos_existentes' => $todosPagos->toArray()
                ]);

                // Intentar extraer el ID de venta del PedidoID (formato: venta-{id}-{timestamp})
                if (preg_match('/^venta-(\d+)-\d+$/', $pedidoId, $matches)) {
                    $ventaId = $matches[1];
                    Log::info('ID de venta extraído del PedidoID', ['venta_id' => $ventaId]);
                    
                    // Buscar cualquier pago de esta venta que esté pendiente
                    $pagoAlternativo = Pago::where('venta_id', $ventaId)
                                          ->where('estado', 'pendiente')
                                          ->orderBy('id', 'desc')
                                          ->first();
                    
                    if ($pagoAlternativo) {
                        Log::warning('Pago encontrado con método alternativo', [
                            'pago_id' => $pagoAlternativo->id,
                            'referencia_original' => $pagoAlternativo->referencia_externa,
                            'referencia_callback' => $pedidoId
                        ]);
                        $pago = $pagoAlternativo;
                        
                        // Actualizar la referencia externa para que coincida
                        $pago->update(['referencia_externa' => $pedidoId]);
                    }
                }
                
                if (!$pago) {
                    return response()->json([
                        'error' => 1,
                        'estatus' => 0,
                        'message' => "Pago no encontrado en el sistema",
                        'values' => false
                    ]);
                }
            }

            // Procesar según el estado del pago
            $estadoInterno = 'pendiente';
            
            // La lógica principal es verificar si el estado indica "completado" 
            // o si es un estado numérico específico, o si contiene "PROCESADO"
            $estadoLower = strtolower($estado);
            if ($estadoLower === 'completado' || 
                $estadoLower === 'pagado' || 
                $estado === '2' ||
                str_contains($estadoLower, 'procesado') ||
                str_contains($estadoLower, 'completado - procesado')) {
                $estadoInterno = 'completado';
            } elseif ($estadoLower === 'rechazado' || 
                     $estadoLower === 'cancelado' || 
                     $estado === '3') {
                $estadoInterno = 'rechazado';
            } else {
                // Para otros casos, mantener como pendiente
                // El callback de PagoFácil normalmente solo se envía cuando hay un cambio de estado significativo
                Log::info('Estado no reconocido en callback, manteniendo como pendiente', [
                    'estado_recibido' => $estado,
                    'pedido_id' => $pedidoId
                ]);
            }

            // Actualizar el pago en nuestra base de datos
            $pago->update([
                'estado' => $estadoInterno,
                'fecha_pago' => now(),
                'metodo_pago' => 'PAGO_FACIL_' . strtoupper($metodoPago ?? 'QR'),
                'datos_pago' => json_encode([
                    'callback_data' => $request->all(),
                    'fecha_callback' => now(),
                    'metodo_pago_pagofacil' => $metodoPago,
                    'fecha_pago_pagofacil' => $fecha,
                    'hora_pago_pagofacil' => $hora
                ])
            ]);

            // Si el pago fue completado, actualizar también el estado del pedido
            if ($estadoInterno === 'completado') {
                $this->actualizarEstadoPedido($pago);
            }

            Log::info('Pago actualizado exitosamente desde callback', [
                'pago_id' => $pago->id,
                'pedido_id' => $pedidoId,
                'estado_anterior' => $pago->getOriginal('estado'),
                'estado_nuevo' => $estadoInterno,
                'metodo_pago' => $metodoPago,
                'fecha_pago' => $fecha . ' ' . $hora,
                'pedido_actualizado' => $estadoInterno === 'completado' ? 'SI' : 'NO'
            ]);

            // Si el pago fue completado, actualizar también el estado del pedido
            if ($estadoInterno === 'completado') {
                $this->actualizarEstadoPedido($pago);
            }

            // Respuesta exitosa según especificación de PagoFácil
            return response()->json([
                'error' => 0,
                'estatus' => 1,
                'message' => "Pago procesado correctamente",
                'values' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Error en callback de PagoFácil', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'data' => $request->all()
            ]);

            // Respuesta de error según especificación de PagoFácil
            return response()->json([
                'error' => 1,
                'estatus' => 0,
                'message' => "No se pudo procesar el pago, por favor intente de nuevo",
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

    /**
     * Página de prueba para el callback (solo para desarrollo)
     */
    public function testCallback()
    {
        return Inertia::render('PagoFacil/CallbackTest');
    }

    /**
     * Método de debugging para ver los pagos en la base de datos
     */
    public function debugPagos(Request $request)
    {
        $pagos = Pago::with('venta')
                    ->select('id', 'venta_id', 'referencia_externa', 'transaction_id', 'estado', 'monto', 'fecha')
                    ->orderBy('id', 'desc')
                    ->limit(20)
                    ->get();

        return response()->json([
            'pagos' => $pagos->map(function ($pago) {
                return [
                    'id' => $pago->id,
                    'venta_id' => $pago->venta_id,
                    'referencia_externa' => $pago->referencia_externa,
                    'transaction_id' => $pago->transaction_id,
                    'estado' => $pago->estado,
                    'monto' => $pago->monto,
                    'fecha' => $pago->fecha,
                ];
            }),
            'total_pagos' => Pago::count(),
            'pagos_pendientes' => Pago::where('estado', 'pendiente')->count(),
            'pagos_completados' => Pago::where('estado', 'completado')->count(),
        ]);
    }

    /**
     * Obtener estado de un pago por su referencia externa
     */
    public function obtenerEstadoPago(Request $request)
    {
        try {
            $referencia = $request->input('referencia');
            
            if (!$referencia) {
                return response()->json([
                    'success' => false,
                    'message' => 'Referencia es requerida'
                ], 400);
            }

            // Buscar el pago por referencia externa
            $pago = Pago::where('referencia_externa', $referencia)->first();

            if (!$pago) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pago no encontrado'
                ], 404);
            }

            // Si hay transaction_id, consultar también el estado en PagoFácil
            $estadoPagoFacil = null;
            if ($pago->transaction_id) {
                try {
                    $tokenResponse = $this->obtenerToken();
                    $accessToken = $tokenResponse['values'] ?? null;

                    if ($accessToken) {
                        $client = new Client();
                        $response = $client->post(config('pagofacil.base_url') . '/api/servicio/consultartransaccion', [
                            'headers' => [
                                'Accept' => 'application/json',
                                'Authorization' => 'Bearer ' . $accessToken
                            ],
                            'json' => [
                                'TransaccionDePago' => $pago->transaction_id
                            ]
                        ]);

                        $result = json_decode($response->getBody()->getContents(), true);
                        $estadoPagoFacil = $result['values'] ?? null;
                        
                        // Si obtenemos el estado de PagoFácil, verificar si debemos actualizar nuestro registro
                        if ($estadoPagoFacil && $pago->estado !== 'completado') {
                            $horaPago = $estadoPagoFacil['HoraPago'] ?? null;
                            $fechaPago = $estadoPagoFacil['FechaPago'] ?? null;
                            $messageEstado = $estadoPagoFacil['messageEstadoDescription'] ?? '';
                            
                            // Verificar si el pago está completado según múltiples criterios
                            $tieneHoraYFecha = ($horaPago !== null && $fechaPago !== null);
                            $estadoProcesado = $messageEstado && (
                                str_contains($messageEstado, 'PROCESADO') || 
                                str_contains($messageEstado, 'COMPLETADO - PROCESADO')
                            );
                            
                            if ($tieneHoraYFecha || $estadoProcesado) {
                                $pago->update([
                                    'estado' => 'completado',
                                    'fecha_pago' => now(),
                                    'datos_pago' => json_encode($estadoPagoFacil)
                                ]);
                                
                                // Actualizar el estado del pedido a COMPLETADO
                                $this->actualizarEstadoPedido($pago);
                                
                                Log::info('Pago actualizado como completado desde obtenerEstadoPago', [
                                    'pago_id' => $pago->id,
                                    'referencia' => $referencia,
                                    'tiene_hora_y_fecha' => $tieneHoraYFecha,
                                    'estado_procesado' => $estadoProcesado,
                                    'hora_pago' => $horaPago,
                                    'fecha_pago' => $fechaPago,
                                    'message_estado' => $messageEstado
                                ]);
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Error consultando estado en PagoFácil', [
                        'transaction_id' => $pago->transaction_id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'pago' => [
                    'id' => $pago->id,
                    'venta_id' => $pago->venta_id,
                    'referencia_externa' => $pago->referencia_externa,
                    'transaction_id' => $pago->transaction_id,
                    'estado' => $pago->estado,
                    'monto' => $pago->monto,
                    'fecha' => $pago->fecha,
                    'fecha_pago' => $pago->fecha_pago,
                    'metodo_pago' => $pago->metodo_pago
                ],
                'estado_pagofacil' => $estadoPagoFacil
            ]);

        } catch (\Exception $e) {
            Log::error('Error obteniendo estado de pago', [
                'referencia' => $request->input('referencia'),
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Actualizar el estado del pedido cuando se completa un pago
     */
    private function actualizarEstadoPedido($pago)
    {
        try {
            // Buscar la venta asociada al pago
            $venta = Venta::with('pedido')->find($pago->venta_id);
            
            if (!$venta || !$venta->pedido) {
                Log::warning('No se encontró venta o pedido asociado al pago', [
                    'pago_id' => $pago->id,
                    'venta_id' => $pago->venta_id
                ]);
                return false;
            }

            // Verificar si el pedido ya está completado
            if ($venta->pedido->estado === 'COMPLETADO') {
                Log::info('Pedido ya está marcado como COMPLETADO', [
                    'pedido_id' => $venta->pedido->id,
                    'pago_id' => $pago->id
                ]);
                return true;
            }

            // Actualizar el estado del pedido a COMPLETADO
            $venta->pedido->update(['estado' => 'COMPLETADO']);
            
            Log::info('Pedido actualizado como COMPLETADO', [
                'pedido_id' => $venta->pedido->id,
                'venta_id' => $venta->id,
                'pago_id' => $pago->id,
                'estado_anterior' => $venta->pedido->getOriginal('estado'),
                'estado_nuevo' => 'COMPLETADO'
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Error al actualizar estado del pedido', [
                'pago_id' => $pago->id,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            return false;
        }
    }
}