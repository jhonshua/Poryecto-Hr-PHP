<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimbradoCancelacionesAsimilados extends Model
{
    protected $connection = 'generica';
    protected $table = 'timbrado_cancelaciones_asimilados';
    public $timestamps = false;
    protected $fillable = [ 'id_empleado',
                            'id_periodo',
                            'fecha_cancelacion',
                            'request_cancel',
                            'response',
                            'xml_acuse_cancel',
                            'sello_sat',
                            'file_acuse',
                            'file_soap',
                            'no_factura'
                          ];
    
}