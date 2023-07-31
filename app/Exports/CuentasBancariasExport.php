<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use App\Models\Empleado;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CuentasBancariasExport implements FromCollection,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
      cambiarBase(Session::get('base'));
      $usuario_departamentos = Session::get('usuarioDepartamentos');

      $empleados = Empleado::select('empleados.id','empleados.numero_empleado', 'empleados.id_bancario', 'empleados.nombre', 'empleados.apaterno', 'empleados.amaterno', 'empleados.rfc', 'empleados.curp', 'empleados.fecha_nacimiento', 'empleados.lugar_nacimiento', 'empleados.genero', 'empleados.estado_civil', 'bancos.nombre as banco', 'empleados.cuenta_bancaria', 'empleados.cuenta_bancaria2', 'empleados.cuenta_bancaria3', 'empleados.clabe_interbancaria', 'empleados.tipo_cuenta')
      ->join('singh.bancos', 'empleados.id_banco', '=', 'bancos.id')
      ->where('estatus', 1)
      ->whereIn('id_departamento', $usuario_departamentos)
      ->orderBy('apaterno', 'asc')->get();

      return $empleados;
    }

    public function headings():array{
        return [
            "ID",
            "NUM EMPLEADO",
            "ID BANCARIO",
            "NOMBRE",
            "APATERNO",
            "AMATERNO",
            "RFC",
            "CURP",
            "FECHA NACIMIENTO",
            "LUGAR NACIMIENTO",
            "GENERO",
            "ESTADO CIVIL",
            "BANCO",
            "CUENTA BANCARIA 1",
            "CUENTA BANCARIA 2",
            "CUENTA BANCARIA 3",
            "CLABE INTERBANCARIA",
            "TIPO CUENTA",
        ];
    }
}
