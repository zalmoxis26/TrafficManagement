<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Anexo
 *
 * @property $id
 * @property $descripcion
 * @property $archivo
 * @property $created_at
 * @property $updated_at
 *
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Anexo extends Model
{
    
    protected $perPage = 20;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['descripcion', 'archivo','asunto'];

    public function traficos()
    {
        return $this->belongsToMany(Trafico::class, 'trafico_anexo');
    }

}
