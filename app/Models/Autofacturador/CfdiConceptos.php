<?php

namespace App\Models\Autofacturador;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CfdiConceptos extends Model
{
    use SoftDeletes;

    protected $table = 'cfdi_conceptos';
    protected $connection = 'empresa';
    protected $guarded = [];
}
