<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;
    
    public $timestamps = true;
    
    protected $fillable = [
        'venta_id', 
        'monto', 
        'fecha', 
        'metodo_pago',
        'referencia_externa',
        'transaction_id',
        'datos_pago',
        'fecha_pago',
        'estado'
    ];

    protected $casts = [
        'datos_pago' => 'array',
        'fecha_pago' => 'datetime',
        'fecha' => 'datetime'
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    /**
     * Scope para pagos completados
     */
    public function scopeCompletados($query)
    {
        return $query->where('estado', 'completado');
    }

    /**
     * Scope para pagos pendientes
     */
    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    /**
     * Verificar si el pago está completado
     */
    public function isCompletado()
    {
        return $this->estado === 'completado';
    }

    /**
     * Verificar si el pago está pendiente
     */
    public function isPendiente()
    {
        return $this->estado === 'pendiente';
    }
}
