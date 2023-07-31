<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AsignaEmpresasEmisoras extends Model
{
    use HasFactory;
    protected $table = 'asigna_empresas_emisoras';
    protected $primaryKey = 'id';
    const CREATED_AT = 'fecha_creacion';

    function emisora(){
        return $this->hasMany('App\Models\EmpresaEmisora', 'id', 'id_empresa_e');
    }

}
