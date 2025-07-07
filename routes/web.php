<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\NotaController;
use App\Http\Controllers\PromocionController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\BusquedaGlobalController;
use App\Http\Controllers\PagoFacilController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    \App\Http\Middleware\VisitasMiddleware::class,
])->group(function () {
    Route::get('/dashboard', [ReporteController::class, 'index'])->name('dashboard');
    
    //Notas
    Route::get('/notas', [NotaController::class, 'index'])->name('notas.index');
    Route::get('/notas/create', [NotaController::class, 'create'])->name('notas.create');
    Route::post('/notas', [NotaController::class, 'store'])->name('notas.store');
    Route::get('/notas/{nota}/edit', [NotaController::class, 'edit'])->name('notas.edit');
    Route::put('/notas/{nota}', [NotaController::class, 'update'])->name('notas.update');
    Route::delete('/notas/{nota}', [NotaController::class, 'destroy'])->name('notas.destroy');

    //promociones
    Route::delete('/promociones/{promocion}', [PromocionController::class, 'destroy']);
    Route::put('/promociones/{promocion}', [PromocionController::class, 'update'])->name('promociones.update');
    Route::resource('promociones', PromocionController::class)->except(['update']);

});

// routes/web.php
Route::resource('productos', ProductoController::class)
    ->middleware([
        'auth',                      // sigue autenticando
        'role:gerente,cajero',       // ← aquí la protección por rol
        \App\Http\Middleware\VisitasMiddleware::class,
    ]);


Route::resource('pedidos', PedidoController::class)->middleware([
    'auth', // si tienes auth
    \App\Http\Middleware\VisitasMiddleware::class, // tu middleware
]);

Route::resource('ventas', VentaController::class)->middleware([
    'auth', // si tienes auth
    \App\Http\Middleware\VisitasMiddleware::class, // tu middleware
]);

Route::resource('usuarios', UsuarioController::class)->middleware([
    'auth',
    \App\Http\Middleware\VisitasMiddleware::class,
]);

// Stripe routes
Route::get('ventas/{venta}/stripe', [StripeController::class, 'form'])->name('stripe.form');
Route::post('stripe/procesar', [StripeController::class, 'procesar'])->name('stripe.procesar');
Route::get('stripe/success/{venta}', [StripeController::class, 'success'])->name('stripe.success');
Route::get('/ventas/{id}/success', [StripeController::class, 'success'])->name('stripe.success');
Route::get('/stripe/cancel', [StripeController::class, 'cancel'])->name('stripe.cancel');

// Pago Fácil routes
Route::middleware([
    'auth',
    \App\Http\Middleware\VisitasMiddleware::class,
])->group(function () {
    Route::get('/pagofacil', [PagoFacilController::class, 'index'])->name('pagofacil.index');
    Route::post('/pagofacil/generar-qr', [PagoFacilController::class, 'generarQR'])->name('pagofacil.generar-qr');
    Route::post('/pagofacil/consultar-estado', [PagoFacilController::class, 'consultarEstado'])->name('pagofacil.consultar-estado');
    Route::get('/pagofacil/return', [PagoFacilController::class, 'return'])->name('pagofacil.return');
    // Página de prueba del callback (solo en desarrollo)
    Route::get('/pagofacil/test-callback', [PagoFacilController::class, 'testCallback'])->name('pagofacil.test-callback');
    // Debug de pagos (solo en desarrollo)
    Route::get('/pagofacil/debug-pagos', [PagoFacilController::class, 'debugPagos'])->name('pagofacil.debug-pagos');
    // Obtener estado de pago por referencia
    Route::post('/pagofacil/obtener-estado-pago', [PagoFacilController::class, 'obtenerEstadoPago'])->name('pagofacil.obtener-estado-pago');
});

// Callback público para Pago Fácil (sin middleware de autenticación)
Route::post('/pagofacil/callback', [PagoFacilController::class, 'callback'])->name('pagofacil.callback');

// Búsqueda routes
Route::get('/buscar-global', [BusquedaGlobalController::class, 'buscar'])->name('buscar.global')->middleware([
    'auth',
    \App\Http\Middleware\VisitasMiddleware::class,
]);
Route::get('/busqueda', [BusquedaGlobalController::class, 'buscar'])->name('busqueda.index')->middleware([
    'auth',
    \App\Http\Middleware\VisitasMiddleware::class,
]);

