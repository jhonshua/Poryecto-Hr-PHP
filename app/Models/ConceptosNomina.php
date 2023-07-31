<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConceptosNomina extends Model
{
    use HasFactory;
    protected $connection = 'empresa';
    protected $table = 'conceptos_nomina';
    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_edicion';
    protected $guarded = [];
}
