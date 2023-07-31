<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Audiencia extends Model
{
    protected $connection = 'empresa';
    protected $table = 'demandas_audiencias';
    public $timestamps = false;
    public $fillable = ['documento_laudo'];

    public function evidencias(){
        return $this->hasMany('App\Models\EvidenciaJuridico', 'id_audiencia', 'id');
    }

    public function involucrados(){
        return $this->hasMany('App\Models\InvolucradoAudiencia', 'id_audiencia', 'id');
    }
}
