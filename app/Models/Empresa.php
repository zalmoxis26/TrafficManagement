<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Empresa
 *
 * @property $id
 * @property $clave
 * @property $descripcion
 * @property $rfc
 * @property $created_at
 * @property $updated_at
 *
 * @property UsersEmpresa[] $usersEmpresas
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Empresa extends Model
{
    
    protected $perPage = 20;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['clave', 'descripcion', 'rfc','empresaMatriz'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function usersEmpresas()
    {
        return $this->hasMany(\App\Models\UsersEmpresa::class, 'id', 'empresa_id');
    }
    

    public function traficos()
    {
        return $this->hasMany(Trafico::class, 'id', 'empresa_id');
    }

}
