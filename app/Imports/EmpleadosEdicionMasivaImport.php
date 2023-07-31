<?php

namespace App\Imports;

use App\Models\Empleado;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\{Importable, ToCollection, WithHeadingRow, WithValidation, SkipsFailures};

class EmpleadosEdicionMasivaImport implements ToCollection, WithHeadingRow
{
    use Importable, SkipsFailures;

    protected $tipo;

    public function  __construct($tipo)
    {
        $this->tipo = $tipo;
    }


    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        cambiarBase(Session::get('base'));
        foreach ($rows as $row){
            $ids[] = $row['id'];
        }
        $empleados = Empleado::select('id', 'sueldo_neto', 'salario_digital', 'salario_diario', 'salario_diario_integrado', 'fecha_antiguedad', 'id_categoria')
                            ->whereIn('id', $ids)->get();
        $empleados = $empleados->keyBy('id');

        
        foreach ($rows as $row){

            if($this->tipo == 'sNeto' && !empty($row['id'])){

                if($row['sueldo_neto'] != $empleados[$row['id']]->sueldo_neto){

                    DB::connection('empresa')->table('modificaciones_sueldo')->insert([
                        'id_empleado' => $row['id'],
                        'sueldo_anterior' => $empleados[$row['id']]->sueldo_neto,
                        'sueldo_nuevo' => $row['sueldo_neto'],
                        'sueldo_real_anterior' => $empleados[$row['id']]->salario_digital,
                        'sueldo_real_nuevo' => $row['salario_digital'],
                        'fecha_creacion' => date('Y-m-d H:i:s')
                    ]);
                }

                Empleado::where('id', $row['id'])->update([
                        'sueldo_neto' => $row['sueldo_neto'],
                        'salario_digital' => $row['salario_digital']
                ]);


            } else if($this->tipo == 'sDiario' && !empty($row['id'])){

                $fecha_antiguedad = Carbon::parse($empleados[$row['id']]->fecha_antiguedad);
                $hoy = Carbon::now();
                $antiguedad = $fecha_antiguedad->diffInYears($hoy);

                $prestaciones = DB::connection('empresa')->table('prestaciones')
                                    ->select('*')->join('categorias', 'prestaciones.id_categoria', '=', 'categorias.id')
                                    ->where('categorias.id', $empleados[$row['id']]->id_categoria)
                                    ->where('prestaciones.antiguedad', $antiguedad)
                                    ->where('categorias.estatus', 1)
                                    ->where('prestaciones.estatus', 1)
                                    ->first();

                $salario_diario_integrado = round($prestaciones->factor_integracion * $row['salario_diario'], 2);


                if($row['salario_diario'] == $empleados[$row['id']]->salario_diario){
                    $estatus_folio_modificacion = 0;
                } else{
                    $estatus_folio_modificacion = 1;

                    DB::connection('empresa')->table('modificaciones_salario')->insert([
                        'id_empleado' => $row['id'],
                        'salario_anterior' => $empleados[$row['id']]->salario_diario,
                        'salario_nuevo' => $row['salario_diario'],
                        'salario_integrado_anterior' => $empleados[$row['id']]->salario_diario_integrado,
                        'salario_integrado_nuevo' => $salario_diario_integrado,
                        'fecha_creacion' => date('Y-m-d H:i:s')
                    ]);
                }

                Empleado::where('id', $row['id'])->update([
                    'salario_diario' => $row['salario_diario'],
                    'salario_diario_integrado' => $salario_diario_integrado ,
                    'estatus_folio_modificacion' => $estatus_folio_modificacion
                ]);

            }
        }
    }
}
