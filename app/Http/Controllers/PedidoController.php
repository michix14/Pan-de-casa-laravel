<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Visita;
use Carbon\Carbon;


class PedidoController extends Controller
{
    public function index()
    {
        $page_name = request()->path();
        $visita = Visita::where('page_name', $page_name)->first();
        $visitas = $visita ? $visita->cant : 0;

        return Inertia::render('Pedidos/Index', [
            'pedidos' => Pedido::with('usuario')->get(),
            'visitas' => $visitas
        ]);
    }

    public function create()
    {
        return Inertia::render('Pedidos/Create', [
            'usuarios' => User::all()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'usuario_id' => 'required|exists:users,id',
            'tipo' => 'required|in:TIENDA,ENVIO,RECOJO',
            'estado' => 'required|in:PENDIENTE,COMPLETADO,CANCELADO',
            'fecha_entrega' => ['required', 'date', function ($attribute, $value, $fail) {
                $fecha = \Carbon\Carbon::parse($value)->startOfDay();
                $hoy = \Carbon\Carbon::today();

                if ($fecha->lessThan($hoy)) {
                    $fail('La fecha de entrega no puede ser anterior a hoy.');
                }
            }],
        ], [
            'usuario_id.required' => 'El campo usuario es obligatorio.',
            'usuario_id.exists' => 'El usuario seleccionado no es válido.',

            'tipo.required' => 'El campo tipo es obligatorio.',
            'tipo.in' => 'El tipo debe ser TIENDA, ENVIO o RECOJO.',

            'estado.required' => 'El campo estado es obligatorio.',
            'estado.in' => 'El estado debe ser PENDIENTE, COMPLETADO o CANCELADO.',

            'fecha_entrega.required' => 'La fecha de entrega es obligatoria.',
            'fecha_entrega.date' => 'Debe ingresar una fecha válida.',
            'fecha_entrega.after' => 'La fecha de entrega no puede ser anterior a hoy.',
        ]);

        Pedido::create($request->all());

        return redirect()->route('pedidos.index')->with('success', 'Pedido creado exitosamente.');
    }

    public function edit(Pedido $pedido)
    {
        return Inertia::render('Pedidos/Edit', [
            'pedido' => $pedido->load('usuario'),
            'usuarios' => User::all()
        ]);
    }

    public function update(Request $request, Pedido $pedido)
    {
        $request->validate([
            'usuario_id' => 'required|exists:users,id',
            'tipo' => 'required|in:TIENDA,ENVIO,RECOJO',
            'estado' => 'required|in:PENDIENTE,COMPLETADO,CANCELADO',
            'fecha_entrega' => ['required', 'date', function ($attribute, $value, $fail) {
                $fecha = \Carbon\Carbon::parse($value)->startOfDay();
                $hoy = \Carbon\Carbon::today();

                if ($fecha->lessThan($hoy)) {
                    $fail('La fecha de entrega no puede ser anterior a hoy.');
                }
            }],
        ], [
            'usuario_id.required' => 'El campo usuario es obligatorio.',
            'usuario_id.exists' => 'El usuario seleccionado no es válido.',

            'tipo.required' => 'El campo tipo es obligatorio.',
            'tipo.in' => 'El tipo debe ser TIENDA, ENVIO o RECOJO.',

            'estado.required' => 'El campo estado es obligatorio.',
            'estado.in' => 'El estado debe ser PENDIENTE, COMPLETADO o CANCELADO.',

            'fecha_entrega.required' => 'La fecha de entrega es obligatoria.',
            'fecha_entrega.date' => 'La fecha de entrega debe ser una fecha válida.',
            'fecha_entrega.after' => 'La fecha de entrega no puede ser anterior a hoy.',
        ]);

        $pedido->update($request->all());

        return redirect()->route('pedidos.index')->with('success', 'Pedido actualizado exitosamente.');
    }

    public function destroy(Pedido $pedido)
    {
        $pedido->delete();

        return redirect()->route('pedidos.index')->with('success', 'Pedido eliminado exitosamente.');
    }
}
