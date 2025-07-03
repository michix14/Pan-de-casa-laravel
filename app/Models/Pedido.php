<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;
    protected $table = 'pedidos';

    protected $fillable = [
        'usuario_id',
        'tipo',
        'estado',
        'fecha_entrega',
    ];
    public $timestamps = false;

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }
}
