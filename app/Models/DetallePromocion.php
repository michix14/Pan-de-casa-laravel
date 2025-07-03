<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetallePromocion extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'detalle_promocion';

    protected $fillable = [
        'promocion_id',
        'producto_id',
        'precio_promocional',
    ];



    public function promocion()
    {
        return $this->belongsTo(Promocion::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
