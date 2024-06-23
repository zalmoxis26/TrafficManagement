<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;



/**
 * Class Trafico
 *
 * @property $id
 * @property $cliente_id
 * @property $operacion
 * @property $folioTransporte
 * @property $fechaReg
 * @property $Toperacion
 * @property $factura
 * @property $clavePed
 * @property $usDocs
 * @property $Revision
 * @property $Transporte
 * @property $Clasificacion
 * @property $Odt
 * @property $created_at
 * @property $updated_at
 *
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Trafico extends Model
{
   

    protected $perPage = 20;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['statusTrafico','patente','aduana','revision_id', 'pedimento_id','adjuntoFactura','cliente_id', 'empresa_id','embarque', 'folioTransporte', 'fechaReg', 'Toperacion', 'factura', 'clavePed', 'MxDocs', 'Revision', 'Transporte', 'Clasificacion', 'Odt'];


    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id', 'id');
    }
   
    public function comments()
    {
        return $this->hasMany(Comment::class, 'trafico_id', 'id');
    }
    
    
    public function pedimento()
    {
        return $this->belongsTo(Pedimento::class, 'pedimento_id', 'id');
    }

    public function embarques()
    {
        return $this->belongsToMany(Embarque::class, 'trafico_embarque');
    }

    public function revision()
    {
        return $this->belongsTo(Revisione::class);
    }

    public function anexos()
    {
        return $this->belongsToMany(Anexo::class, 'trafico_anexo');
    }

    public function historials()
    {
        return $this->hasMany(Historial::class);
    }

}
