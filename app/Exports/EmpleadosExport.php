<?php

namespace App\Exports;

use App\Models\Puesto;
use App\Models\Horario;
use App\Models\Empleado;
use App\Models\Departamento;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class EmpleadosExport implements FromCollection, WithHeadings
{
    use Exportable;
    
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {

        $export = [];
        cambiarBase(Session::get('base'));
        $usuario_departamentos = Session::get('usuarioDepartamentos');
        // $biometrico = Session::get('empresa.parametros')['biometrico'];
        $sede = Session::get('empresa')['sede'];
        $esSindical = DB::connection('empresa')->table('conceptos_nomina')
                        ->where('file_rool', 0)
                        ->where('estatus', 1)
                        ->count();

        $horarios = Horario::select('id', 'alias')->where('estatus', 1)->get()->keyBy('id');
        $departamentos = Departamento::select('id', 'nombre')->get()->keyBy('id');
        $puestos = Puesto::select('id', 'puesto')->get()->keyBy('id');
        $categorias = DB::connection('empresa')->table('categorias')->get()->keyBy('id');
        $bancos = DB::table('bancos')->get()->keyBy('id');
        if($sede == 1){
            $sedes = DB::connection('empresa')->table('sedes')->get()->keyBy('id');
        } else {
            $sedes = [];
        }

        $empleados =  Empleado::where('estatus', 1)
            ->whereIn('id_departamento', $usuario_departamentos)
            ->orderBy('apaterno', 'asc')
            ->get();
        
        foreach($empleados as $empleado){
            // $fecha_nacimiento = Carbon::parse($empleado->fecha_nacimiento);
            // $hoy = Carbon::now();
            // $edad = $fecha_nacimiento->diffInYears($hoy);

            if($empleado->tipo_cuenta == 01){
                $tipo_cuenta="CHEQUES";
            }else if($empleado->tipo_cuenta == 03){
                $tipo_cuenta="TARJETA DE DÉBITO";
            }else if($empleado->tipo_cuenta == 40){
                $tipo_cuenta="CLABE";
            }else{
                $tipo_cuenta="";
            }

            $export[] = [
                "id" => $empleado->id,
                "numero_empleado" => ($empleado->numero_empleado) ?? $empleado->id,
                "apaterno" => $empleado->apaterno,
                "amaterno" => $empleado->amaterno,
                "nombre" => $empleado->nombre,
                "rfc" => $empleado->rfc,
                "curp" => $empleado->curp,
                "fecha_nacimiento" => $empleado->fecha_nacimiento,
                // "edad" => $edad,
                "fecha_alta" => $empleado->fecha_alta,
                "lugar_nacimiento" => $empleado->lugar_nacimiento,
                "genero" => $empleado->genero,
                "id_categoria" => (isset($categorias[$empleado->id_categoria])) ? $categorias[$empleado->id_categoria]->nombre : '',               
                "id_categoria_asimilados" =>(isset($categorias[$empleado->id_categoria_asimilados])) ? $categorias[$empleado->id_categoria_asimilados]->nombre : '', //////////////
                "id_departamento" => (isset($departamentos[$empleado->id_departamento])) ? $departamentos[$empleado->id_departamento]->nombre : '', ////
                "ubicacion" => $empleado->ubicacion,
                "tipo_jornada" => $empleado->tipo_jornada,
                "tipo_contrato" => $empleado->tipo_contrato,
                "nss" => $empleado->nss,
                "tipo_de_nomina" => $empleado->tipo_de_nomina,
                "salario_diario" => $empleado->salario_diario,
                "sueldo_neto" => $empleado->sueldo_neto,
                "salario_digital" => $empleado->salario_digital,
                "nacionalidad" => $empleado->nacionalidad,
                "calle_numero" => $empleado->calle_numero,
                "colonia" => $empleado->colonia,
                "delegacion" => $empleado->delegacion,
                "estado" => $empleado->estado,
                "cp" => $empleado->cp,
                "correo" => $empleado->correo,
                "telefono_casa" => $empleado->telefono_casa,
                "telefono_movil" => $empleado->telefono_movil,
                "estado_civil" => $empleado->estado_civil,
                "escolaridad" => $empleado->escolaridad,
                "profesion" => $empleado->profesion,
                "avisar_a" => $empleado->avisar_a,
                "avisar_a_telefono" => $empleado->avisar_a_telefono,
                "beneficiario" => $empleado->beneficiario,
                "beneficiario_parentesco" => $empleado->beneficiario_parentesco,
                "num_credito_infonavit" => $empleado->num_credito_infonavit,
                "tipo_descuento" => $empleado->tipo_descuento,
                "valor_descuento" => $empleado->valor_descuento,
                "num_credito_fonacot" => $empleado->num_credito_fonacot,
                "valor_fonacot" => $empleado->valor_fonacot,
                "fecha_antiguedad" => $empleado->fecha_antiguedad,
                "id_puesto" => (isset($puestos[$empleado->id_puesto])) ? $puestos[$empleado->id_puesto]->puesto : '', /////////
                "tipo_empleado" => $empleado->tipo_empleado,
                "tipo_salario" => $empleado->tipo_salario,
                "clabe_interbancaria" => $empleado->clabe_interbancaria,
                "id_banco" => (isset($bancos[$empleado->id_banco])) ? $bancos[$empleado->id_banco]->nombre : '', //////
                "cuenta_bancaria" => $empleado->cuenta_bancaria,
                "tipo_cuenta" => $tipo_cuenta,
                "horario" => (isset($horarios[$empleado->id_horario])) ? $horarios[$empleado->id_horario]->alias : '', ///////////////////
                "sede" => (isset($sedes[$empleado->sede])) ? $sedes[$empleado->sede]->nombre : '', //////////////////////
            ];
        }
        

        return collect($export);
    }

    public function headings(): array
    {
        return [
            "ID",
            "NUMERO EMPLEADO",
            "APATERNO",
            "AMATERNO",
            "NOMBRE",
            "RFC",
            "CURP",
            "FECHA NACIMIENTO",
            // "EDAD",
            "FECHA ALTA",
            "LUGAR NACIMIENTO",
            "GENERO",
            "CATEGORIA", 
            "CATEGORIA-ASIMILADOS",
            "DEPARTAMENTO",
            "UBICACION",
            "TIPO JORNADA",
            "TIPO CONTRATO",
            "NSS",
            "TIPO DE NOMINA",
            "SALARIO DIARIO",
            "SUELDO NETO",
            "SALARIO DIGITAL",
            "NACIONALIDAD",
            "CALLE NUMERO",
            "COLONIA",
            "DELEGACION",
            "ESTADO",
            "CP",
            "CORREO",
            "TELEFONO CASA",
            "TELEFONO MOVIL",
            "ESTADO CIVIL",
            "ESCOLARIDAD",
            "PROFESION",
            "AVISAR A",
            "AVISAR A (TELÉFONO)",
            "BENEFICIARIO",
            "BENEFICIARIO PARENTESCO",
            "NUM CREDITO INFONAVIT",
            "TIPO DESCUENTO",
            "VALOR DESCUENTO",
            "NUM CREDITO FONACOT",
            "VALOR FONACOT",
            "FECHA ANTIGUEDAD",
            "PUESTO",
            "TIPO EMPLEADO",
            "TIPO SALARIO",
            "CLABE INTERBANCARIA",
            "BANCO",
            "CUENTA BANCARIA",
            "TIPO CUENTA",
            "HORARIO",
            "SEDE",
        ];
    }
}
