<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Pago Fácil Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración para la integración con Pago Fácil
    |
    */

    'base_url' => env('PAGOFACIL_BASE_URL', 'https://serviciostigomoney.pagofacil.com.bo'),
    
    'token_service' => env('PAGOFACIL_TOKEN_SERVICE'),
    
    'token_secret' => env('PAGOFACIL_TOKEN_SECRET'),
    
    'commerce_id' => env('PAGOFACIL_COMMERCE_ID'),
    
    // URLs de callback y retorno
    'callback_url' => env('PAGOFACIL_CALLBACK_URL', '/pagofacil/callback'),
    
    'return_url' => env('PAGOFACIL_RETURN_URL', '/pagofacil/return'),
    
    // Configuraciones adicionales
    'timeout' => env('PAGOFACIL_TIMEOUT', 30),
    
    'currency' => 2, // 2 = BOB (Bolivianos)
    
    // Habilitar/deshabilitar logs
    'enable_logs' => env('PAGOFACIL_ENABLE_LOGS', true),
    
    // Entorno (sandbox o production)
    'environment' => env('PAGOFACIL_ENVIRONMENT', 'sandbox'),
];
