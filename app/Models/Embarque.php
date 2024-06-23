<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Embarque
 *
 * @property $id
 * @property $numEconomico
 * @property $entregado
 * @property $Desaduanado
 * @property $claveNombre
 * @property $tipoOper
 * @property $claveAduana
 * @property $fechaEmbarque
 * @property $created_at
 * @property $updated_at
 *
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Embarque extends Model
{
    
    protected $perPage = 20;

    protected $table = 'embarque'; 

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [ 'anden','Transporte','entregaDocs','Caat','Placas','chofer','TipoDeTransporte','numEconomico', 'numEmbarque' , 'tipoOper',  'fechaEmbarque', 'rojoAduana','modulado'];

    public function traficos()
    {
        return $this->belongsToMany(Trafico::class, 'trafico_embarque');
    }

    public function comments(){
        return $this->hasMany(Comment::class, 'embarque_id', 'id');
    }


}
