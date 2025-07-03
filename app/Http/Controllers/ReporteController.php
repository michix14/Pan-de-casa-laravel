<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Inertia\Inertia;

class ReporteController extends Controller
{
    public function index()
    {
        // Conteos por estado (basado en la relación con pedidos)
        $ventasCompletadas = Venta::whereHas('pedido', function ($q) {
            $q->where('estado', 'COMPLETADO');
        })->count();

        $ventasPendientes = Venta::whereHas('pedido', function ($q) {
            $q->where('estado', 'PENDIENTE');
        })->count();

        $ventasCanceladas = Venta::whereHas('pedido', function ($q) {
            $q->where('estado', 'CANCELADO');
        })->count();

        // Monto total completado
        $montoTotal = Venta::whereHas('pedido', function ($q) {
            $q->where('estado', 'COMPLETADO');
        })->sum('total');

        // Ventas por día (solo completadas)
        $ventasPorDia = Venta::whereHas('pedido', function ($q) {
            $q->where('estado', 'COMPLETADO');
        })
        ->selectRaw('DATE(fecha) as fecha, SUM(total) as total')
        ->groupBy('fecha')
        ->orderBy('fecha')
        ->get();

        return Inertia::render('Dashboard', [
            'completadas' => $ventasCompletadas,
            'pendientes' => $ventasPendientes,
            'canceladas' => $ventasCanceladas,
            'montoTotal' => $montoTotal,
            'ventasPorDia' => $ventasPorDia,
        ]);
    }
}
