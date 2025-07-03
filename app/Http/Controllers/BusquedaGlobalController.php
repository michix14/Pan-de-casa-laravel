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
        $query = strtolower($request->input('q'));

        return Inertia::render('BusquedaGlobal', [
            'usuarios' => User::whereRaw('LOWER(name) LIKE ?', ["%{$query}%"])
                            ->orWhereRaw('LOWER(email) LIKE ?', ["%{$query}%"])
                            ->get(),
            'productos' => Producto::whereRaw('LOWER(nombre) LIKE ?', ["%{$query}%"])->get(),
            'ventas' => Venta::whereRaw('LOWER(codigo) LIKE ?', ["%{$query}%"])->get(),
            'query' => $query,
        ]);
    }
}
