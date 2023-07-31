<?php

namespace App\Exports;

use App\Models\Empleado;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class EmpleadosEdicionMasivaExport implements FromCollection, WithHeadings
{
    use Exportable;
    protected $tipo;

    public function __construct($tipo = null)
    {
        $this->tipo = $tipo;
    }
    
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        cambiarBase(Session::get('base'));
        $usuario_departamentos = Session::get('usuarioDepartamentos');
        if($this->tipo == 'sNeto'){
            $select = ["id", "numero_empleado", "apaterno", "amaterno", "nombre", "sueldo_neto", "salario_digital"];
        } else if($this->tipo == 'sDiario'){
            $select = ["id", "numero_empleado", "apaterno", "amaterno", "nombre", "salario_diario"];
        }

        // call_user_func_array([$query, 'select'], $select);
        $empleados =  Empleado::select($select)
            ->where('estatus', 1)
            ->whereIn('id_departamento', $usuario_departamentos)
            ->orderBy('id_banco', 'asc')
            ->orderBy('apaterno', 'asc')
            ->get();

        return $empleados;
    }

    public function headings(): array
    {
        if($this->tipo == 'sDiario'){
            return [
                'id',
                'numero_empleado',
                'apaterno',
                'amaterno',
                'nombre',
                'salario_diario',
            ];
        } else if($this->tipo == 'sNeto'){

            return [
                'id',
                'numero_empleado',
                'apaterno',
                'amaterno',
                'nombre',
                'sueldo_neto',
                'salario_digital'
            ];
        }
    }
}
