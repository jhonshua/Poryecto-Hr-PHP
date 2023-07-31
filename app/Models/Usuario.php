<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    public $timestamps = false;
    protected $connection = 'singh';
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nombre_completo',
        'email',
        'password',
        'estatus',
        'admin',
        'email_jefe',
        'email_ejecutivo',
        'terminos',
        'avisos',
        'fecha_creacion',
        'fecha_edicion',
        'autofacturador',
        'comision',
        'pagar_del',
        'timbrar',
        'base_autofacturador',
        'id_vendedor'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    public function empresas() {
        return $this->belongsToMany('\App\Models\Empresa','usuarios_empresas', 'id_usuario', 'id_empresa')
            ->withPivot('id_empresa')
            ->where('usuarios_empresas.estatus', 1)
            ->orderBy('razon_social');
    }

    public function clientes(){
        return $this->hasOne('\App\Models\Autofacturador\Clientes','id','base_autofacturador');
    }

    public function clientesAutofacturas (){
        return $this->belongsToMany( \App\Models\Autofacturador\Clientes::class, 'rel_usuario_autofacturacion', 'id_usuario', 'id_autofacturacion')
            ->withPivot('id_autofacturacion');
    }
}
