<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visita extends Model
{
    //
    protected $fillable = [
        'page_name',
        'cant'
    ];

    public function visited(){
        $this->cant++;
        $this->save();
    }

}
