<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = ['venta_id', 'monto', 'fecha', 'metodo_pago'];

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }
}
