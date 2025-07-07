<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\Visita;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BusquedaGlobalController extends Controller
{
    public function buscar(Request $request)
    {
        $query = $request->input('q');
        
        // Si no hay término de búsqueda, retornar vista vacía
        if (!$query || trim($query) === '') {
            $page_name = request()->path();
            $visita = Visita::where('page_name', $page_name)->first();
            $visitas = $visita ? $visita->cant : 0;
            
            return Inertia::render('Busqueda/Index', [
                'resultados' => [
                    'usuarios' => [],
                    'productos' => [],
                    'ventas' => [],
                ],
                'busqueda' => '',
                'visitas' => $visitas,
            ]);
        }

        $queryLower = strtolower(trim($query));

        // Buscar en usuarios (por nombre y email)
        $usuarios = User::whereRaw('LOWER(name) like ?', ["%{$queryLower}%"])
            ->orWhereRaw('LOWER(email) like ?', ["%{$queryLower}%"])
            ->limit(10) // Limitar resultados para performance
            ->get();

        // Buscar en productos (por nombre, descripción y posiblemente categoría)
        $productos = Producto::whereRaw('LOWER(nombre) like ?', ["%{$queryLower}%"])
            ->orWhereRaw('LOWER(descripcion) like ?', ["%{$queryLower}%"])
            ->limit(10)
            ->get();

        // Buscar en ventas (por estado y tipo del pedido, y también por ID de venta)
        $ventas = Venta::with('pedido')
            ->where(function($q) use ($queryLower) {
                $q->whereRaw('CAST(id as CHAR) like ?', ["%{$queryLower}%"]) // Buscar por ID de venta
                  ->orWhereHas('pedido', function($subQ) use ($queryLower) {
                      $subQ->whereRaw('LOWER(estado) like ?', ["%{$queryLower}%"])
                           ->orWhereRaw('LOWER(tipo) like ?', ["%{$queryLower}%"]);
                  });
            })
            ->limit(10)
            ->get();

        // Obtener el total de visitas de la página actual
        $page_name = request()->path();
        $visita = Visita::where('page_name', $page_name)->first();
        $visitas = $visita ? $visita->cant : 0;

        // Retornar vista con todos los resultados
        return Inertia::render('Busqueda/Index', [
            'resultados' => [
                'usuarios' => $usuarios,
                'productos' => $productos,
                'ventas' => $ventas,
            ],
            'busqueda' => $query,
            'visitas' => $visitas,
        ]);
    }

}
