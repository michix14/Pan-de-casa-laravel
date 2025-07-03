<?php

namespace App\Http\Controllers;

use App\Models\Promocion;
use App\Models\Producto;
use App\Models\DetallePromocion;
use App\Models\Visita;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class PromocionController extends Controller
{
    public function index()
    {
        $hoy = Carbon::now()->toDateString();

        // Solo leer visitas sin incrementar
        $page_name = request()->path(); // "promociones"
        $visita = Visita::where('page_name', $page_name)->first();
        $visitas = $visita ? $visita->cant : 0;

        $promociones = Promocion::with(['detalles.producto'])
            ->orderBy('fecha_inicio', 'desc')
            ->get()
            ->map(function ($promo) use ($hoy) {
                $promo->activa = $hoy >= $promo->fecha_inicio && $hoy <= $promo->fecha_fin;
                return $promo;
            });

        return Inertia::render('Promociones/Index', [
            'promociones' => $promociones,
            'visitas' => $visitas,
        ]);
    }

    public function create()
    {
        $productos = Producto::orderBy('nombre')->get();

        return Inertia::render('Promociones/Create', [
            'productos' => $productos,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => 'required|exists:productos,id',
            'detalles.*.precio_promocional' => 'required|numeric|min:0',
        ]);

        $promo = Promocion::create([
            'nombre' => $validated['nombre'],
            'fecha_inicio' => $validated['fecha_inicio'],
            'fecha_fin' => $validated['fecha_fin'],
        ]);

        foreach ($validated['detalles'] as $item) {
            DetallePromocion::create([
                'promocion_id' => $promo->id,
                'producto_id' => $item['producto_id'],
                'precio_promocional' => $item['precio_promocional'],
            ]);
        }

        return to_route('promociones.index');
    }

    public function edit($id)
    {
        $promocion = Promocion::with('detalles.producto')->findOrFail($id);
        $productos = Producto::all();

        return Inertia::render('Promociones/Edit', [
            'promocion' => $promocion,
            'productos' => $productos,
            'visitas' => Visita::count(),
        ]);
    }

    public function update(Request $request, Promocion $promocion)
    {
        try {
            $request->validate([
                'nombre' => 'required|string|max:255',
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
                'productos' => 'required|array|min:1',
                'productos.*.producto_id' => 'required|exists:productos,id',
                'productos.*.precio_promocional' => 'required|numeric|min:0',
            ]);

            // Actualiza la promoción
            $promocion->update([
                'nombre' => $request->nombre,
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
            ]);

            // Elimina los detalles anteriores
            DetallePromocion::where('promocion_id', $promocion->id)->delete();

            // Inserta los nuevos detalles
            foreach ($request->productos as $producto) {
                DetallePromocion::create([
                    'promocion_id' => $promocion->id,
                    'producto_id' => $producto['producto_id'],
                    'precio_promocional' => str_replace(',', '.', $producto['precio_promocional']),
                ]);
            }

            return redirect()->route('promociones.index')->with('success', 'Promoción actualizada correctamente.');
        } catch (\Exception $e) {
            return redirect()->route('promociones.index')->with('error', 'Ocurrió un error al actualizar la promoción.');
        }
    }



    public function destroy(Promocion $promocion)
    {
        // Precaución: si por alguna razón no está activa la cascada
        DB::beginTransaction();
        try {
            $promocion->detalles()->delete(); // esto asegura que se borren
            $promocion->delete();

            DB::commit();
            return back()->with('success', 'Promoción eliminada correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }
}
