<?php

namespace App\Exports;
use App\Models\Empleado;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PrestacionesExtrasExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        cambiarBase(Session::get('base'));
        $usuario_departamentos = Session::get('usuarioDepartamentos');
        $empleados = Empleado::select( 'empleados.id as empId', 'empleados.numero_empleado', 'empleados.apaterno', 'empleados.amaterno', 'empleados.nombre', 'empleados.rfc', 'empleados.fecha_antiguedad', 'prestaciones_extras.estatus', 'prestaciones_extras.num_certificado', 'prestaciones_extras.valor_seguro_GM', 'prestaciones_extras.valor_plan_espejo')
                        ->where('empleados.estatus', [Empleado::EMPLEADO_ACTIVO])
                        ->whereIn('empleados.id_departamento', $usuario_departamentos)
                        ->leftJoin('prestaciones_extras', 'empleados.id', '=', 'prestaciones_extras.id_empleado')
                        ->orderBy('empleados.apaterno', 'asc')
                        ->get();
        foreach ($empleados as $empleado) {
            if(empty($empleado->estatus) || $empleado->estatus == 0) $empleado->estatus = 'no'; 
            else if($empleado->estatus == 1) $empleado->estatus = 'si'; 
        }
        return $empleados;
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
            "FECHA ANTIGUEDAD",
            "ACTIVO PRESTACIONES",
            "NUMERO CERTIFICADO",
            "VALOR SEGURO GASTOS M",
            "VALOR PLAN ESPEJO",
        ];
    }
}
