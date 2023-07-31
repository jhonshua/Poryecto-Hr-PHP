<?php

namespace App\Models\Biometrico;

use Illuminate\Database\Eloquent\Model;

class asignacionBiometrico extends Model
{
    
    protected $connection = 'empresa';
    //protected $table = 'departamentos';
    protected $fillable = ['id_biometrico','id_empleado'];
    protected $guarded = [];
    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_edicion';
    public function asignados(){
        return $this->hasMany('\App\Models\Empleado','id','id_empleado');
    }


}
