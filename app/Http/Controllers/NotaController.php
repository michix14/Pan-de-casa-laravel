<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Nota;
use App\Models\Producto;
use App\Models\DetalleNota;
use Inertia\Inertia;
use App\Models\Visita;
use Illuminate\Support\Facades\DB;

class NotaController extends Controller
{

    public function index()
    {
        $notas = Nota::orderBy('fecha_realizado', 'desc')->get();

        // Lógica para visitas (igual que en productos)
        $page_name = request()->path(); // Esto devolverá "notas"
        $visita = Visita::where('page_name', $page_name)->first();
        $visitas = $visita ? $visita->cant : 0;

        return Inertia::render('Notas/Index', [
            'notas' => $notas,
            'visitas' => $visitas, // 👈 IMPORTANTE
        ]);
    }

    public function create()
    {
        $productos = \App\Models\Producto::select('id', 'nombre', 'precio')->get();
        return Inertia::render('Notas/Create', [
            'productos' => $productos
        ]);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'fecha_realizado' => 'required|date',
            'tipo_nota' => 'required|in:entrada,salida',
            'detalles' => 'required|array|min:1',
            'detalles.*.id_producto' => 'required|exists:productos,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
        ], [
            'required' => 'El campo :attribute es obligatorio.',
            'in' => 'El campo :attribute debe ser entrada o salida.',
            'integer' => 'El campo :attribute debe ser un número entero.',
            'min' => 'El valor mínimo permitido para :attribute es :min.',
            'exists' => 'El producto seleccionado no existe.',
        ]);

        DB::beginTransaction();
        try {
            $nota = Nota::create([
                'fecha_realizado' => $validated['fecha_realizado'],
                'tipo_nota' => $validated['tipo_nota'],
            ]);

            $total = 0;

            foreach ($validated['detalles'] as $item) {
                $producto = Producto::find($item['id_producto']);
                $subtotal = $producto->precio * $item['cantidad'];
                $total += $subtotal;

                // Actualiza stock según tipo de nota
                if ($validated['tipo_nota'] === 'entrada') {
                    $producto->stock += $item['cantidad'];
                } else {
                    if ($producto->stock < $item['cantidad']) {
                        throw new \Exception("No hay suficiente stock del producto {$producto->nombre}");
                    }
                    $producto->stock -= $item['cantidad'];
                }
                $producto->save();

                DetalleNota::create([
                    'id_nota' => $nota->id_nota,
                    'id_producto' => $item['id_producto'],
                    'cantidad' => $item['cantidad'],
                    'subtotal' => $subtotal,
                ]);
            }

            $nota->total = $total;
            $nota->save();

            DB::commit();

            return redirect()
                ->route('notas.index') // o la ruta que tengas
                ->with('success', 'Nota registrada correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function edit(Nota $nota)
    {
        $nota->load('detalles.producto'); // Para traer detalles y productos

        $productos = Producto::all();

        return Inertia::render('Notas/Edit', [
            'nota' => $nota,
            'productos' => $productos,
        ]);
    }

    public function update(Request $request, Nota $nota)
    {
        $validated = $request->validate([
            'fecha_realizado' => 'required|date',
            'tipo_nota' => 'required|in:entrada,salida',
            'detalles' => 'required|array|min:1',
            'detalles.*.id_producto' => 'required|exists:productos,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            // Revertir el stock anterior
            foreach ($nota->detalles as $detalle) {
                $producto = Producto::find($detalle->id_producto);
                if ($nota->tipo_nota === 'entrada') {
                    $producto->stock -= $detalle->cantidad;
                } else {
                    $producto->stock += $detalle->cantidad;
                }
                $producto->save();
            }

            // Actualizar encabezado de la nota
            $nota->update([
                'fecha_realizado' => $validated['fecha_realizado'],
                'tipo_nota' => $validated['tipo_nota'],
            ]);

            // Eliminar los detalles anteriores
            $nota->detalles()->delete();

            $total = 0;


            // Insertar nuevos detalles y ajustar stock
            foreach ($validated['detalles'] as $item) {
                $producto = Producto::find($item['id_producto']);
                $subtotal = $producto->precio * $item['cantidad'];
                $total += $subtotal;

                if ($validated['tipo_nota'] === 'entrada') {
                    $producto->stock += $item['cantidad'];
                } else {
                    if ($producto->stock < $item['cantidad']) {
                        throw new \Exception("No hay suficiente stock del producto {$producto->nombre}");
                    }
                    $producto->stock -= $item['cantidad'];
                }
                $producto->save();

                $nota->detalles()->create([
                    'id_producto' => $item['id_producto'],
                    'cantidad' => $item['cantidad'],
                    'subtotal' => $subtotal,
                ]);
            }

            $nota->total = $total;
            $nota->save();

            DB::commit();

            return redirect()->route('notas.index')->with('success', 'Nota actualizada correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }


    public function destroy(Nota $nota)
    {
        $nota->detalles()->delete();
        $nota->delete();

        return redirect()->route('notas.index')->with('success', 'Nota eliminada');
    }
}
