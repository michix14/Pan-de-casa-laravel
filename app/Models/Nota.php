<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nota extends Model
{
    protected $table = 'notas';
    protected $primaryKey = 'id_nota';
    public $timestamps = false;

    protected $fillable = [
        'fecha_realizado',
        'tipo_nota',
        'total',
    ];

    public function detalles()
    {
        return $this->hasMany(DetalleNota::class, 'id_nota');
    }
}
