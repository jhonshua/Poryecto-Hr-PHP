<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaldoNomina extends Model
{
    protected $connection = 'empresa';
    protected $table = 'saldo_nomina';
    protected $guarded = [];
    public $timestamps = false;
}
