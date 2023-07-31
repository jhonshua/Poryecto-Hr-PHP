<?php
namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;


    class Empleado extends Model
{
    protected $connection = 'empresa';
    protected $table = 'empleados';
    protected $guarded = [];
    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_edicion';
    const LONGITUD_PASSWORD = 7;
    public const EMPLEADO_ACTIVO = 1; // activo normal
    public const EMPLEADO_INACTIVO = 0; // inactivo
    public const EMPLEADO_ELIMINADO = 5; // eliminado
    public const EMPLEADO_TEMPORAL = 30; // en proceso de ser creado
    public const EMPLEADO_DESHABILITADO = 10; // deshabilitado
    public const EMPLEADO_BAJA = 2; // Dado de baja (posible reingreso)
    public const EMPLEADO_BAJA_DEFINITIVO = 20; // Baja definitiva y finiquitado

    /**
     * Relacion con Departamento
     */
    public function departamento() {
        return $this->hasOne('App\Models\Departamento', 'id', 'id_departamento');
    }

    //traer el contrato vigente del empleado
    public function contrato(){
        return $this->hasOne('App\Models\ContratoEmpleado', 'id_empleado', 'id')->where("estatus",1);
    }

    //traer los contratos anteriores
    public function contratos(){
        return $this->hasMany('App\Models\ContratoEmpleado', 'id_empleado', 'id')
        ->where(function ($query) {
            $query->where('estatus', 1)
            ->orWhere('estatus', 0);
        })->orderBy('id','desc');
    }

    /**
     * Relacion con Categoria
     */
    public function categoria() {
        return $this->hasOne('App\Models\Categoria', 'id', 'id_categoria');
    }

    /**
     * Relacion con Puestos
     */
    public function puesto() {
        return $this->hasOne('App\Models\Puesto', 'id', 'id_puesto');
    }

    /**
     * Relacion con Puestos
     */
    public function horario() {
        return $this->hasOne('App\Models\Horario', 'id', 'id_horario');
    }

    /**
     * Relacion con Sede
     */
    public function sede() {
        return $this->hasOne('App\Models\Sede', 'id', 'sede');
    }
    public function sde() {
        return $this->hasOne('App\Models\Sede', 'id', 'sede');
    }

    /**
     * Relacion con Campos extras
     */
    public function camposExtras() {
        return $this->hasMany('App\Models\EmpleadoInformacionExtra', 'id_empleado', 'id');
    }

    /**
     * Relacion con Incapacidades
     */
    public function incapacidadesActivas() {
        return $this->hasMany('App\Models\Incapacidad', 'id_empleado', 'id')->where('estatus', 1);
    }

    /**
     * Get the user's full name.
     *
     * @return string
     */
    public function getNombreCompletoAttribute()
    {
        return "{$this->apaterno} {$this->amaterno} {$this->nombre}";
    }

    /**
     * Obtiene empleados activos de una BD
     */
    public function obtenerEmpleados($base = null)
    {
        if(empty($base)) return null;

        if(cambiarBase($base)) {
            $empleados = Empleado::where('estatus', 1)
                                ->orderBy('apaterno', 'asc')
                                ->get();
            return $empleados;
        }
    }

    /**
     * Obtiene empleados segun los departamentos asignados al usuario logueado
     */
    public function obtenerEmpleadosSegunDepartamentosAsignados($base = null)
    {

        if(empty($base)) return null;
            $departamentos = DB::connection('empresa')
                                ->table('departamentos')
                                ->orderBy('id', 'desc')
                                ->get();

            foreach ($departamentos as $key => $value) {
               $id_deptos[] = $value->id;
            }

            $empleados = Empleado::with('departamento', 'puesto', 'camposExtras','registro_covid')
                                ->whereIn('estatus', [Empleado::EMPLEADO_ACTIVO, Empleado::EMPLEADO_ELIMINADO, Empleado::EMPLEADO_TEMPORAL, Empleado::EMPLEADO_DESHABILITADO])
                                ->whereIn('id_departamento', $id_deptos)
                                ->orderBy('nombre', 'asc')
                                ->get();
            
            $empleados = $empleados->keyBy('id');
            
            // Sacamos si tiene un contrato activo y su fecha de vencimiento
            $emplepadosIds = [];
            foreach($empleados as $emp){
                $emplepadosIds[] = $emp->id;
                $emp->contratoActivo = false;
                $emp->contratoFechaVencimiento = '';
                $emp->existioContrato = false;
            }

            $contratos = DB::connection('empresa')
                                ->table('contratos')
                                ->whereIn('id_empleado', $emplepadosIds)
                                ->orderBy('id', 'desc')
                                ->get();


            foreach($contratos as $contrato){
                $empleados[$contrato->id_empleado]->contratoActivo = ($contrato->fecha_vencimiento > date('Y-m-d H:i:s') || ($contrato->fecha_vencimiento == '0000-00-00 00:00:00' || $contrato->fecha_vencimiento == NULL)) ? true : false;
                $empleados[$contrato->id_empleado]->contratoFechaVencimiento = $contrato->fecha_vencimiento;
                $empleados[$contrato->id_empleado]->existioContrato = true;
                $empleados[$contrato->id_empleado]->contratoEstatus = $contrato->estatus;
            }
            return $empleados;
    }

    /**
     * Obtiene empleado por ID
     */
    public function obtenerEmpleadoPorID($base = null, $id = null)
    {
        if(!$base || !$id) return null;

        if(cambiarBase($base)) {
            $empleado = Empleado::find($id);
            return $empleado;
        }
    }

    /**
     * Obtiene empleado por email
     */
    public function obtenerEmpleadoPorEmail($base = null, $email = null)
    {
        if(!$base || !$email) return null;

        cambiarBase($base);
        $empleado = Empleado::where('correo', $email)->first();
        return $empleado;
    }

    /**
     * 
     */
    public function generarPassword($long = self::LONGITUD_PASSWORD)
    {
        $characters = '23456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $password = '';
        for ($i = 0; $i < $long; $i++) {
            $password .= $characters[rand(0, $charactersLength - 1)];
        }
        return $password;
    }

    /*      Modulo covid        */

    public function registro_covid(){
        return $this->hasMany('App\Models\Covid\RegistroCovid', 'id_empleado', 'id')->orderBy('fecha_inicio','desc')->with('contactos','lo_contagio','evidencias');
    }

    public function es_contacto(){
        return $this->hasMany('App\Models\Covid\ContactosCovid', 'id_empleado', 'id')->where('id_registro',null)->with('es_contacto_de');

    }

}
