<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BusquedaGlobalController extends Controller
{
    public function buscar(Request $request)
    {
        $query = $request->input('q');

        // Buscar primero en usuarios
        $usuarios = User::where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->get();

        if ($usuarios->count() > 0) {
            return redirect()->route('usuarios.index')->with('busqueda', $query);
        }

        // Buscar en productos
        $productos = Producto::where('nombre', 'like', "%{$query}%")
            ->orWhere('descripcion', 'like', "%{$query}%")
            ->get();

        if ($productos->count() > 0) {
            return redirect()->route('productos.index')->with('busqueda', $query);
        }

        // Buscar en ventas
        $ventas = Venta::where('tipo', 'like', "%{$query}%")
            ->orWhere('estado', 'like', "%{$query}%")
            ->get();

        if ($ventas->count() > 0) {
            return redirect()->route('ventas.index')->with('busqueda', $query);
        }

        // Si no hay coincidencias
        return redirect()->back()->with('mensaje', 'No se encontraron resultados.');
    }

}
