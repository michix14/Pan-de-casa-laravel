<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Pago;
use App\Models\Venta;

class VerificarPagos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pagofacil:verificar-pagos {--limpiar : Eliminar pagos huérfanos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verificar el estado de los pagos de PagoFácil y encontrar inconsistencias';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Verificando pagos de PagoFácil...');
        $this->newLine();

        // Estadísticas generales
        $totalPagos = Pago::count();
        $pagosPendientes = Pago::where('estado', 'pendiente')->count();
        $pagosCompletados = Pago::where('estado', 'completado')->count();
        $pagosRechazados = Pago::where('estado', 'rechazado')->count();

        $this->info("📊 Estadísticas generales:");
        $this->line("   Total de pagos: {$totalPagos}");
        $this->line("   Pendientes: {$pagosPendientes}");
        $this->line("   Completados: {$pagosCompletados}");
        $this->line("   Rechazados: {$pagosRechazados}");
        $this->newLine();

        // Verificar referencias externas
        $this->info("🔗 Verificando referencias externas:");
        $pagosConReferencia = Pago::whereNotNull('referencia_externa')->count();
        $pagosSinReferencia = Pago::whereNull('referencia_externa')->count();
        
        $this->line("   Con referencia externa: {$pagosConReferencia}");
        if ($pagosSinReferencia > 0) {
            $this->warn("   Sin referencia externa: {$pagosSinReferencia}");
        }

        // Verificar formato de referencias
        $referenciasIncorrectas = Pago::whereNotNull('referencia_externa')
            ->where('referencia_externa', 'not regexp', '^venta-[0-9]+-[0-9]+$')
            ->get();

        if ($referenciasIncorrectas->count() > 0) {
            $this->warn("⚠️  Referencias con formato incorrecto:");
            foreach ($referenciasIncorrectas as $pago) {
                $this->line("   ID: {$pago->id}, Referencia: {$pago->referencia_externa}");
            }
        } else {
            $this->info("✅ Todas las referencias tienen formato correcto (venta-ID-timestamp)");
        }
        $this->newLine();

        // Verificar ventas asociadas
        $this->info("🛒 Verificando ventas asociadas:");
        $pagosConVentaInvalida = Pago::whereNotExists(function ($query) {
            $query->select(DB::raw(1))
                  ->from('ventas')
                  ->whereColumn('ventas.id', 'pagos.venta_id');
        })->get();

        if ($pagosConVentaInvalida->count() > 0) {
            $this->error("❌ Pagos con venta_id inválido:");
            foreach ($pagosConVentaInvalida as $pago) {
                $this->line("   Pago ID: {$pago->id}, Venta ID: {$pago->venta_id}");
            }
        } else {
            $this->info("✅ Todos los pagos tienen ventas válidas");
        }
        $this->newLine();

        // Mostrar últimos pagos
        $this->info("📋 Últimos 10 pagos:");
        $ultimosPagos = Pago::orderBy('id', 'desc')->limit(10)->get();
        
        $headers = ['ID', 'Venta ID', 'Referencia Externa', 'Estado', 'Monto', 'Fecha'];
        $rows = $ultimosPagos->map(function ($pago) {
            return [
                $pago->id,
                $pago->venta_id,
                $pago->referencia_externa ?: 'N/A',
                $pago->estado,
                'Bs ' . number_format($pago->monto, 2),
                $pago->fecha ? $pago->fecha->format('Y-m-d H:i') : 'N/A'
            ];
        })->toArray();

        $this->table($headers, $rows);
        $this->newLine();

        // Opción de limpieza
        if ($this->option('limpiar')) {
            $this->info("🧹 Iniciando limpieza...");
            
            if ($pagosConVentaInvalida->count() > 0) {
                if ($this->confirm("¿Eliminar {$pagosConVentaInvalida->count()} pagos con venta_id inválido?")) {
                    $eliminados = 0;
                    foreach ($pagosConVentaInvalida as $pago) {
                        $pago->delete();
                        $eliminados++;
                    }
                    $this->info("✅ Eliminados {$eliminados} pagos huérfanos");
                }
            }
            
            // Limpiar pagos muy antiguos pendientes (más de 24 horas)
            $pagosAntiguosPendientes = Pago::where('estado', 'pendiente')
                ->where('fecha', '<', now()->subDay())
                ->get();
                
            if ($pagosAntiguosPendientes->count() > 0) {
                if ($this->confirm("¿Marcar como expirados {$pagosAntiguosPendientes->count()} pagos pendientes de más de 24 horas?")) {
                    foreach ($pagosAntiguosPendientes as $pago) {
                        $pago->update(['estado' => 'expirado']);
                    }
                    $this->info("✅ Marcados {$pagosAntiguosPendientes->count()} pagos como expirados");
                }
            }
        }

        $this->newLine();
        $this->info("✨ Verificación completada");
        
        if (!$this->option('limpiar')) {
            $this->comment("💡 Usa --limpiar para ejecutar tareas de limpieza");
        }
    }
}
