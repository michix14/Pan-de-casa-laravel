<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Pedido;
use App\Models\DetalleVenta;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Visita;
use Carbon\Carbon;
use DB;

class VentaController extends Controller
{
    public function index()
    {
        $page_name = request()->path();
        $visita = Visita::where('page_name', $page_name)->first();
        $visitas = $visita ? $visita->cant : 0;

        return Inertia::render('Ventas/Index', [
            'ventas' => Venta::with('pedido.usuario', 'detalles.producto')->get(),
            'visitas' => $visitas
        ]);
    }

    public function create()
    {
        return Inertia::render('Ventas/Create', [
            'usuarios' => User::all(),
            'productos' => Producto::where('stock', '>', 0)->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'usuario_id' => 'required|exists:users,id',
            'tipo' => 'required|in:TIENDA,ENVIO,RECOJO',
            'estado' => 'required|in:PENDIENTE,COMPLETADO,CANCELADO',
            'fecha_entrega' => ['required', 'date', function ($attribute, $value, $fail) {
                $fecha = Carbon::parse($value)->startOfDay();
                $hoy = Carbon::today();
                if ($fecha->lt($hoy)) {
                    $fail('La fecha de entrega no puede ser anterior a hoy.');
                }
            }],
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => 'required|exists:productos,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
        ], [
            'usuario_id.required' => 'El usuario es obligatorio.',
            'tipo.required' => 'El tipo es obligatorio.',
            'estado.required' => 'El estado es obligatorio.',
            'fecha_entrega.required' => 'La fecha de entrega es obligatoria.',
            'detalles.required' => 'Debe ingresar al menos un producto.',
        ]);

        DB::beginTransaction();

        try {
            // Crear el pedido
            $pedido = Pedido::create([
                'usuario_id' => $request->usuario_id,
                'tipo' => $request->tipo,
                'estado' => $request->estado,
                'fecha_entrega' => $request->fecha_entrega,
            ]);

            // Calcular total
            $totalVenta = 0;
            $detalles = [];

            foreach ($request->detalles as $detalle) {
                $producto = Producto::findOrFail($detalle['producto_id']);

                if ($producto->stock < $detalle['cantidad']) {
                    throw new \Exception("Stock insuficiente para el producto {$producto->nombre}");
                }

                $subtotal = $producto->precio * $detalle['cantidad'];
                $totalVenta += $subtotal;

                // Guardar detalle temporal para luego
                $detalles[] = [
                    'producto_id' => $producto->id,
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $producto->precio,
                    'total' => $subtotal,
                ];

                // Descontar stock
                $producto->decrement('stock', $detalle['cantidad']);
            }

            // Crear la venta
            $venta = Venta::create([
                'pedido_id' => $pedido->id,
                'fecha' => now(),
                'total' => $totalVenta,
            ]);

            // Guardar los detalles
            foreach ($detalles as $d) {
                DetalleVenta::create([
                    'venta_id' => $venta->id,
                    'producto_id' => $d['producto_id'],
                    'cantidad' => $d['cantidad'],
                    'precio_unitario' => $d['precio_unitario'],
                    'total' => $d['total'],
                ]);
            }

            DB::commit();
            return redirect()->route('ventas.index')->with('success', 'Venta registrada exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show(Venta $venta)
    {
        return Inertia::render('Ventas/Show', [
            'venta' => $venta->load('pedido.usuario', 'detalles.producto')
        ]);
    }
}
