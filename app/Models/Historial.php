<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Historial extends Model
{
    use HasFactory;


    protected $fillable = ['trafico_id', 'nombre', 'descripcion', 'hora','adjunto'];

    public function trafico()
    {
        return $this->belongsTo(Trafico::class);
    }
}
