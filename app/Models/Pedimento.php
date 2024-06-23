<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Pedimento
 *
 * @property $id
 * @property $numPedimento
 * @property $aduana
 * @property $patente
 * @property $clavePed
 * @property $fechaPed
 * @property $adjunto
 * @property $created_at
 * @property $updated_at
 *
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Pedimento extends Model
{
    
    protected $perPage = 20;
    protected $table = 'pedimento'; // Nombre de la tabla correcto

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['operacion','numPedimento', 'aduana', 'patente', 'clavePed', 'fechaPed', 'adjunto','remesa','fechaDodaPita'];


        public function traficos()
    {
        return $this->hasMany(Trafico::class);
    }

}
