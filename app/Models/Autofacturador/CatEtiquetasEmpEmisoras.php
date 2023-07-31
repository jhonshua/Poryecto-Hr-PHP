<?php

namespace App\Models\Autofacturador;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatEtiquetasEmpEmisoras extends Model
{
    use HasFactory;
    protected $table = 'cat_etiquetas_emisoras';
    protected $connection = 'empresa';
    protected $guarded = [];
    public $timestamps = false;

    //traer empresas para cada etiqueta
    public function empresas(){
        return $this->hasMany('App\Models\Autofacturador\EmpresasEmisoras', 'id_cat_etiqueta_emisora', 'id')
            ->select('id', 'razon_social', 'id_cat_etiqueta_emisora')
            ->where(function ($query) {
                $query->where('estatus', 1);
            });
    }
}
