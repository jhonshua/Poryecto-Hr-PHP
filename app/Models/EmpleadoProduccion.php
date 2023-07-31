<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use App\Events\AvisoSistema;
use App\Events\EmailGenerico;

class EmpleadoProduccion extends Model
{
    protected $connection = 'empresa';
    protected $table = 'empleados';
    protected $guarded = [];
    const CREATED_AT = 'creado';
    const UPDATED_AT = 'editado';
    protected $primaryKey = 'id';

    const LONGITUD_PASSWORD = 7;
    public const EMPLEADO_ACTIVO = 1; // activo normal
    public const EMPLEADO_INACTIVO = 0; // inactivo
    public const EMPLEADO_ELIMINADO = 5; // eliminado
    public const EMPLEADO_TEMPORAL = 30; // en proceso de ser creado
    public const EMPLEADO_DESHABILITADO = 10; // deshabilitado
    public const EMPLEADO_BAJA = 2; // Dado de baja (posible reingreso)
    public const EMPLEADO_BAJA_DEFINITIVO = 20; // Baja definitiva y finiquitado

    public function getNombreCompletoAttribute()
    {
        return "{$this->apaterno} {$this->amaterno} {$this->nombre}";
    }

    
    public function getEstatusAttribute(){
        return $this->status;
    }

    public function getFechaAntiguedadAttribute(){
        return $this->attributes['fecha_antiguedad'];
    }

    public function getTelefonoMovilAttribute(){
        return $this->attributes['telefono_movil'];
    }

   
    public function obtenerEmpleados($base = null)
    {
        if(empty($base)) return null;

        if(cambiarBase($base)) {
            $empleados = EmpleadoProduccion::where('estatus', 1)
                                ->orderBy('apaterno', 'asc')
                                ->get();
            return $empleados;
        }
    }


    public function obtenerEmpleadosSegunDepartamentosAsignados($base = null)
    {
        if(empty($base)) return null;

        if(cambiarBase($base)) {
            $empleados = EmpleadoProduccion::with('departamento', 'puesto', 'camposExtras')
                                ->whereIn('estatus', [EmpleadoProduccion::EMPLEADO_ACTIVO, EmpleadoProduccion::EMPLEADO_ELIMINADO, EmpleadoProduccion::EMPLEADO_TEMPORAL, EmpleadoProduccion::EMPLEADO_DESHABILITADO])
                                ->whereIn('id_departamento', Session::get('usuarioDepartamentos'))
                                ->orderBy('apaterno', 'asc')
                                ->get();
            $empleados = $empleados->keyBy('id');
     
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
            }
            
            return $empleados;
        }
    }

 
    public function obtenerEmpleadoPorID($base = null, $id = null)
    {
        if(!$base || !$id) return null;

        cambiarBase($base);
        $empleado = EmpleadoProduccion::find($id);
        return $empleado;

    }

   
    public function obtenerEmpleadoPorEmail($base = null, $email = null)
    {
        if(!$base || !$email) return null;

        if(cambiarBase($base)) {
            $empleado = EmpleadoProduccion::where('correo', $email)->first();
            return $empleado;
        }
    }

    
    public function generarPassword()
    {
        $characters = '23456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $password = '';
        for ($i = 0; $i < self::LONGITUD_PASSWORD; $i++) {
            $password .= $characters[rand(0, $charactersLength - 1)];
        }
        return $password;
    } 
}
