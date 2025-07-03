<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\NotaController;
use App\Http\Controllers\PromocionController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\VentaController;


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
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

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

Route::resource('productos', ProductoController::class)->middleware([
    'auth', // si tienes auth
    \App\Http\Middleware\VisitasMiddleware::class, // tu middleware
]);

Route::resource('pedidos', PedidoController::class)->middleware([
    'auth', // si tienes auth
    \App\Http\Middleware\VisitasMiddleware::class, // tu middleware
]);

Route::resource('ventas', VentaController::class)->middleware([
    'auth', // si tienes auth
    \App\Http\Middleware\VisitasMiddleware::class, // tu middleware
]);


