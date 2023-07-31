<?php

namespace App\Models;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
class Vcard_info extends Model
{
    protected $connection = 'empresa';
    protected $table = 'vcards_info';
    public $timestamps = false;
    protected $fillable = [
                            'idvcard',
                            'contacto',
                            'tipocontacto'];
}