<?php

namespace App\Models;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
class Vcardg extends Model
{
    protected $connection = 'empresa';
    protected $table = 'vcards';
    //public $timestamps = false;
    protected $fillable = [
                            'idempresa',
                            'direccion',
                            'colorfndinp',
                            'colorfndbtninp',
                            'coloricnbtninp',
                            'fndbodyvcardinp',
                            'recfondlistintinp',
                            'recletlistinitinp',
                            'reclistinticonsinp',
                            'logo_empresa_empleado'];
}