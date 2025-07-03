<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Pago;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\Models\DetalleVenta;
use Illuminate\Support\Facades\DB;

class StripeController extends Controller
{
    public function form($ventaId)
    {
        $venta = Venta::with('pedido.usuario', 'detalles.producto')->findOrFail($ventaId);

        return Inertia::render('Ventas/StripeForm', [
            'venta' => $venta,
            'stripeKey' => config('services.stripe.key')
        ]);
    }

    public function procesar(Request $request)
    {
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
        $venta = Venta::findOrFail($request->venta_id);
        $usdTotal = $venta->total / 7.21;

        if ($usdTotal < 0.50) {
            return redirect()->back()->with('error', 'El monto mínimo para pagar con tarjeta es $0.50 USD (≈ Bs 3.61)');
        }
        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => 'Venta #' . $request->venta_id,
                    ],
                    'unit_amount' => intval(($venta->total / 7.21) * 100),
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => url('ventas/' . $request->venta_id . '/success'),
            'cancel_url' => url('stripe/cancel?venta_id=' . $venta->id),
        ]);

        return redirect($session->url);
    }

    public function success($ventaId)
    {
        $venta = Venta::with('pedido')->findOrFail($ventaId);

        Pago::create([
            'venta_id' => $venta->id,
            'monto' => $venta->total,
            'fecha' => now(),
            'metodo_pago' => 'TARJETA',
        ]);

        if ($venta->pedido) {
            $venta->pedido->estado = 'COMPLETADO';
            $venta->pedido->save();
        }

        return redirect()->route('ventas.index')->with('success', 'Pago realizado con éxito y pedido completado.');
    }

    public function cancel(Request $request)
    {
        try {
            DB::beginTransaction();

            $ventaId = $request->query('venta_id');
            $venta = Venta::with(['detalles', 'pedido'])->findOrFail($ventaId);

            if ($venta->pedido) {
                $venta->pedido->estado = 'CANCELADO';
                $venta->pedido->save();
            }

            foreach ($venta->detalles as $detalle) {
                $producto = $detalle->producto;
                if ($producto) {
                    $producto->stock += $detalle->cantidad;
                    $producto->save();
                }
            }

            DB::commit();

            return redirect()->route('ventas.index')->with('error', 'La compra fue cancelada y el stock fue restaurado.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('ventas.index')->with('error', 'Error al cancelar la compra: ' . $e->getMessage());
        }
    }
}
