<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Revisione
 *
 * @property $id
 * @property $nombreRevisor
 * @property $inicioRevision
 * @property $finRevision
 * @property $tiempoRevision
 * @property $created_at
 * @property $updated_at
 *
 * @property Trafico[] $traficos
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Revisione extends Model
{
    
    protected $perPage = 20;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['facturaCorrecta', 'ubicacionRevision' , 'correccionFactura' ,'nombreRevisor', 'inicioRevision', 'finRevision', 'tiempoRevision','adjuntoRevision'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function traficos()
    {
        return $this->hasOne(Trafico::class, 'revision_id', 'id');
    }
    
}
