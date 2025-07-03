<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleNota extends Model
{
    protected $table = 'detalle_notas';
    protected $primaryKey = 'id_detallenota';
    public $timestamps = false;

    protected $fillable = [
        'id_nota',
        'id_producto',
        'cantidad',
        'subtotal',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }
}

