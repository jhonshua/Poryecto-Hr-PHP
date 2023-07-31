<?php

namespace App\Models\Autofacturador;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Cfdi extends Model
{
    use SoftDeletes;
    protected $connection = 'empresa';
    protected $table = 'cfdi';
    protected $guarded = [];

    public const PROCESO_CANCELADO = 0; // activo normal
    public const CFDI_SOLCITADO = 1; // Solicitud enviada
    public const CFDI_APROBADO  = 2; // activo normal
    public const CDFI_FACTURADO = 3; // activo normal
    public const CFDI_CANCELADO = 99; // activo normal

    public function emisora(){
        return $this->hasOne('App\Models\Autofacturador\EmpresasEmisoras', 'id', 'id_emp_emsora');
    }

    public function conceptos(){
        return $this->hasMany('App\Models\Autofacturador\CfdiConceptos', 'id_cfdi', 'id');
    }

    public function usoCFDI(){
        return $this->hasOne('App\Models\Autofacturador\UsoCFDI', 'clave', 'receptor_uso_cfdi');
    }

    public function formaPago(){
        return $this->hasOne('App\Models\Autofacturador\CatFormasPagos', 'clave', 'forma_pago');
    }

    public function metodoPago(){
        return $this->hasOne('App\Models\Autofacturador\CatMetodosPagos', 'metodo', 'metodo_pago');
    }

    public function regimenFiscalEmisor(){
        return $this->hasOne('App\Models\Autofacturador\RegimenFiscal', 'codigo', 'emisor_reg_fiscal');
    }

    public function regimenFiscalReceptor(){
        return $this->hasOne('App\Models\Autofacturador\RegimenFiscal', 'codigo', 'receptor_regimen_fiscal');
    }

    public function cfdiConceptos(){
        return $this->hasMany('App\Models\Autofacturador\CfdiConceptos', 'id_cfdi', 'id');
    }

    public function receptor(){
        return $this->hasOne('App\Models\Autofacturador\DatosFiscalesReceptor', 'id', 'receptor_id');
    }
    public function comprobantesPago(){
        return $this->hasMany('App\Models\Autofacturador\ComprobantesPagos', 'id_cfdi', 'id');
    }

    public function comprobantePago(){
        return $this->hasOne('App\Models\Autofacturador\ComprobantesPagos', 'id_cfdi', 'id');
    }

    public function logs(){
        return $this->hasMany('App\Models\Autofacturador\LogsAutofacturador', 'id_cfdi', 'id')->with('usuarios')->orderBy('id','desc');
    }

    public function ultimoComplementoPago(){
        return $this->hasOne('App\Models\Autofacturador\ComprobantesPagos','id_cfdi', 'id')->orderBy('num_pago', 'desc');
    }

    public function relacionCfdi(){
        return $this->hasOne('App\Models\Autofacturador\Cfdi','id', 'cfdi_relacionado');
    }

}
