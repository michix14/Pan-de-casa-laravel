# Callback de PagoFácil - Documentación

## Resumen

El callback es una notificación automática que PagoFácil envía a nuestro servidor cuando se procesa un pago. Esto nos permite actualizar el estado de los pagos en tiempo real sin depender únicamente del polling.

## Configuración

### 1. URL del Callback

La URL del callback se configura en:
- Archivo: `config/pagofacil.php`
- Variable: `callback_url`
- Valor por defecto: `{APP_URL}/pagofacil/callback`

### 2. Ruta del Callback

La ruta está definida en `routes/web.php`:
```php
Route::post('/pagofacil/callback', [PagoFacilController::class, 'callback'])->name('pagofacil.callback');
```

**Importante**: Esta ruta está fuera del middleware de autenticación para permitir que PagoFácil acceda sin autenticarse.

### 3. Exclusión de CSRF

El callback está excluido de la verificación CSRF en `app/Http/Middleware/VerifyCsrfToken.php`:
```php
protected $except = [
    'pagofacil/callback',
];
```

## Estructura de Datos Recibidos

PagoFácil envía un POST request con los siguientes datos:

```json
{
    "PedidoID": "venta-123-1672531200",
    "Fecha": "2025-01-07",
    "Hora": "14:30:25",
    "MetodoPago": "QR",
    "Estado": "completado"
}
```

### Campos:
- **PedidoID**: Identificador único del pedido (coincide con `referencia_externa` en nuestra DB)
- **Fecha**: Fecha en que se realizó el pago (formato: YYYY-MM-DD)
- **Hora**: Hora en que se realizó el pago (formato: HH:MM:SS)
- **MetodoPago**: Método usado para el pago (QR, TigoMoney, etc.)
- **Estado**: Estado del pago (completado, rechazado, etc.)

## Respuesta Esperada

Nuestro sistema debe responder con la estructura específica que espera PagoFácil:

### Respuesta Exitosa:
```json
{
    "error": 0,
    "estatus": 1,
    "message": "Pago procesado correctamente",
    "values": true
}
```

### Respuesta de Error:
```json
{
    "error": 1,
    "estatus": 0,
    "message": "No se pudo procesar el pago, por favor intente de nuevo",
    "values": false
}
```

## Procesamiento del Callback

### 1. Validación de Datos
- Verificar que se recibió el `PedidoID`
- Buscar el pago en la base de datos usando `referencia_externa`

### 2. Actualización del Estado
- Mapear el estado recibido a nuestros estados internos:
  - `completado`, `pagado`, `2` → `completado`
  - `rechazado`, `cancelado`, `3` → `rechazado`
  - Otros → `pendiente`

### 3. Actualización en Base de Datos
Se actualizan los siguientes campos en la tabla `pagos`:
- `estado`: Nuevo estado del pago
- `fecha_pago`: Timestamp actual
- `metodo_pago`: Prefijo + método recibido
- `datos_pago`: JSON con toda la información del callback

## Estados de Pago

### Estados Internos:
- **pendiente**: Pago creado pero no confirmado
- **completado**: Pago exitosamente procesado
- **rechazado**: Pago rechazado o cancelado

### Estados de PagoFácil:
- **1**: Pendiente
- **2**: Completado/Pagado
- **3**: Rechazado/Cancelado

## Logging

Todos los callbacks se registran en los logs de Laravel con el nivel `info` para callbacks exitosos y `error` para fallos.

Ubicación de logs: `storage/logs/laravel.log`

## Testing

### Página de Prueba
Existe una página de prueba accesible en:
- URL: `/pagofacil/test-callback`
- Solo disponible con autenticación
- Permite simular callbacks para testing

### Uso de la Página de Prueba:
1. Acceder a `/pagofacil/test-callback`
2. Llenar los campos del formulario
3. Usar un `PedidoID` existente en la base de datos
4. Enviar el callback simulado
5. Verificar la respuesta y logs

## Flujo Completo del Pago

1. **Usuario inicia pago** → `generarQR()`
2. **Se crea registro de pago** → Estado: `pendiente`
3. **Se inicia polling** → `consultarEstado()` cada 5 segundos
4. **Usuario realiza pago** → En app bancaria/PagoFácil
5. **PagoFácil procesa pago** → Internamente
6. **PagoFácil envía callback** → `callback()` - Actualización inmediata
7. **Polling detecta cambio** → `consultarEstado()` - Confirmación adicional
8. **Usuario es redirigido** → Página de confirmación

## Consideraciones de Seguridad

1. **URL Pública**: El callback debe ser accesible sin autenticación
2. **Validación**: Siempre validar que el `PedidoID` existe en nuestra base de datos
3. **Logs**: Registrar todos los intentos de callback para auditoría
4. **Rate Limiting**: Considerar limitar requests al endpoint del callback

## Troubleshooting

### Problemas Comunes:

1. **Error 419 (CSRF)**: Verificar que la ruta esté en `$except` del middleware CSRF
2. **Error 404**: Verificar que la ruta esté definida correctamente
3. **Pago no se actualiza**: Verificar que el `PedidoID` coincida con `referencia_externa`
4. **Callback no llega**: Verificar que la URL del callback sea accesible públicamente

### Verificación de URL del Callback:
La URL debe ser accesible desde internet. Para testing local, usar herramientas como ngrok:
```bash
ngrok http 8000
# Usar la URL generada en PAGOFACIL_CALLBACK_URL
```

## Variables de Entorno

Agregar a `.env`:
```env
PAGOFACIL_CALLBACK_URL=https://tu-dominio.com/pagofacil/callback
PAGOFACIL_RETURN_URL=https://tu-dominio.com/pagofacil/return
PAGOFACIL_ENABLE_LOGS=true
```
