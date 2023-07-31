<?php

namespace App\Exports;

use App\Models\Puesto;
use App\Models\Empleado;
use App\Models\ConceptosNomina;
use Illuminate\Support\Facades\DB;
use App\Models\PeriodosNomina;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class exportPrenomina implements FromCollection, WithHeadings, ShouldAutoSize
{

    use Exportable;

    protected $id_periodo;
    protected $columnas;
    protected $dias_imss;

    public function __construct($id_periodo)
    {
        //tienePermisoA('periodos_nomina');
        cambiarBase(Session::get('base'));

        $this->tiene_sedes = Session::get('empresa')['sede'];
        $this->deptos_asignados = Session::get('usuarioDepartamentos');
        $this->id_periodo = decrypt($id_periodo);
        $this->columnas = ConceptosNomina::where('tipo_proceso', 0)->where('estatus', 1)->where('file_rool', '!=', 0)->where('activo_en_nomina',1)->where('nomina', 1)->get();
        $this->dias_imss = Session::get('empresa')['dias_imss'];
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $export = [];
        $periodo = periodosNomina::find($this->id_periodo);
        $puestos = Puesto::select('id', 'puesto')->get()->keyBy('id');
        $sede = Session::get('empresa')['sede'];
        ($sede == 1) ?  $sedes =  DB::connection('empresa')->table('sedes')->get()->keyBy('id') : $sedes = [];


        $empleados = Empleado::where('estatus', Empleado::EMPLEADO_ACTIVO)
            ->where('tipo_de_nomina', 'like',  $periodo->nombre_periodo)
            ->whereIn('id_departamento', $this->deptos_asignados)
            ->where('fecha_alta', '<=', $periodo->fecha_final_periodo)
            ->orderBy('apaterno', 'asc')->get();

        if($this->tiene_sedes == 1){

            $empleados = Empleado::where('estatus', Empleado::EMPLEADO_ACTIVO)
                ->where('tipo_de_nomina', 'like',  $periodo->nombre_periodo)
                ->whereIn('id_departamento', $this->deptos_asignados)
                ->where('fecha_alta', '<=', $periodo->fecha_final_periodo)
                ->orderBy('sede', 'desc')
                ->orderBy('apaterno', 'desc')
                ->get();
        }

        $valores_rutinas_empleados = DB::connection('empresa')->table('rutinas'.$periodo->ejercicio)
            ->where('id_periodo', $periodo->id)
            ->where('fnq_valor', 0)
            ->get()->keyBy('id_empleado');
        
        foreach ($empleados as $empleado){
            //Rene si no existe en rutinas se omite
            if(!$valores_rutinas_empleados->has($empleado->id)){continue;}

            if(Session::get('empresa')['base']=='empresa000041' || Session::get('empresa')['base']=='empresa000176' || Session::get('empresa')['base']=='empresa000177' || Session::get('empresa')['base']=='empresa000178' || Session::get('empresa')['base']=='empresa000179' || Session::get('empresa')['base']=='empresa000183' || Session::get('empresa')['base']=='empresa000184' || Session::get('empresa')['base']=='empresa000046'){
                $row = [
                    'id' => $empleado->id,
                    'num_empleado' => $empleado->numero_empleado,
                    'nombre' => $empleado->nombre_completo,
                    "sede" => (isset($sedes[$empleado->sede])) ? $sedes[$empleado->sede]->nombre : '',
                    'fecha_antiguedad'  => $empleado->fecha_antiguedad,
                    'id_puesto' => (isset($puestos[$empleado->id_puesto])) ? $puestos[$empleado->id_puesto]->puesto : '',
                ];
            }else{

                $row = [
                    'id' => $empleado->id,
                    'num_empleado' => $empleado->numero_empleado,
                    'nombre' => $empleado->nombre_completo,
                ];
            }

            if( array_key_exists('dias_imss', Session::get('usuarioPermisos')) && $this->dias_imss == 1)
                $row[] = $valores_rutinas_empleados[$empleado->id]->dias_imss;

            foreach ($this->columnas as $col){

                $col_valor = "valor".$col->id;
                $row[] =$valores_rutinas_empleados[$empleado->id]->$col_valor;
            }
            $export[] = $row;
        }
    
        return collect($export);
    }

    public function headings(): array
    {
        if(Session::get('empresa')['base']=='empresa000041' || Session::get('empresa')['base']=='empresa000176' || Session::get('empresa')['base']=='empresa000177' || Session::get('empresa')['base']=='empresa000178' || Session::get('empresa')['base']=='empresa000179' || Session::get('empresa')['base']=='empresa000183' || Session::get('empresa')['base']=='empresa000184' || Session::get('empresa')['base']=='empresa000046'){
            
            $headings =  [
                'ID',
                'NUM EMPLEADO',
                'NOMBRE',
                'SEDE',
                'FECHA DE ANTIGUEDAD',
                'PUESTO',
            ];

        }else{

            $headings =  [
                'ID',
                'NUM EMPLEADO',
                'NOMBRE',
            ];
        }

        if( array_key_exists('dias_imss', Session::get('usuarioPermisos')) && $this->dias_imss == 1)
            $headings[] = 'DIAS IMSS';

        foreach ($this->columnas as $col ) {
            
            $campo = trim(strtoupper($col->nombre_concepto));
            $campo = str_replace("Á", "A", $campo);
            $campo = str_replace("É", "E", $campo);
            $campo = str_replace("Í", "I", $campo);
            $campo = str_replace("Ó", "O", $campo);
            $campo = str_replace("Ú", "U", $campo);
            $headings[] = $campo;
        }

        return $headings;
    }
}
