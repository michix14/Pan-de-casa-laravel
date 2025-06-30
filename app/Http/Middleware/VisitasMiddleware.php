<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Visita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class VisitasMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $page_name = request()->path();
        $visita = Visita::where('page_name', $page_name)->first();
        if($visita){
            $visita->visited();
        }else{
            Visita::create([
                'page_name' => $page_name,
                'cant' => 1
            ]);
        }
        return $next($request);
    }
}
