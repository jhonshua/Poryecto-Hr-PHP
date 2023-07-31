<?php

namespace App\Exports;

use App\Models\Empleado;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use App\Models\PeriodosNomina;
use Illuminate\Support\Facades\Session;
use \Maatwebsite\Excel\Sheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Events\AfterSheet;

class AsistenciasExport3_periodo implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents
{
    use Exportable, RegistersEventListeners;

    protected $fecha_inicio;
    protected $fecha_fin;
    protected $comida;
    protected $dias_festivos;
    protected $inasistencias;
    protected $id_periodo;

    public function __construct( $id_periodo)
    {
        $this->id_periodo = $id_periodo;
    }


    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        cambiarBase(Session::get('base'));
        
        $renglon = 2;
        $export = [];
        $asistencias2=[];
        $periodo = PeriodosNomina::find($this->id_periodo);
        $this->fecha_inicio = $periodo->fecha_inicial_periodo;
        $this->fecha_fin = $periodo->fecha_final_periodo;

        $empleados = Empleado::where('estatus', [Empleado::EMPLEADO_ACTIVO])
                        ->orderBy('apaterno', 'asc')
                        ->get()->keyBy('id');

        $asistencias = DB::connection('empresa')->table('asistencia_horario')
                            ->select('id_empleado','dia', 'entrada', 'salida')
                            ->whereBetween('dia', [$this->fecha_inicio, $this->fecha_fin])
                            ->orderBy('id_empleado')
                            ->get();
        
        foreach ($asistencias as $asistencia ) {
            $asistencias2[$asistencia->id_empleado.'_'.$asistencia->dia] = $asistencia;
        }
                            
        
        $fechas_a_mostrar = CarbonPeriod::create($this->fecha_inicio, $this->fecha_fin);

        foreach ($empleados as $empleado ) {

            foreach ($fechas_a_mostrar as $fecha1 ) {
                
                $fecha = $fecha1->format('Y-m-d');
                // dd($fecha);


                if( array_key_exists($empleado->id.'_'.$fecha, $asistencias2) ) {
                    // dd($empleado->id.'_'.$fecha, $asistencias2[$empleado->id.'_'.$fecha]->entrada);
					$horas = 0;
					$entrada = ($asistencias2[$empleado->id.'_'.$fecha]->entrada != NULL)?date('H:i:s', strtotime($asistencias2[$empleado->id.'_'.$fecha]->entrada) ):'SIN REGISTRO';
                    $salida =  ($asistencias2[$empleado->id.'_'.$fecha]->salida != NULL)?date('H:i:s', strtotime($asistencias2[$empleado->id.'_'.$fecha]->salida)):'SIN REGISTRO';
                    
					if($asistencias2[$empleado->id.'_'.$fecha]->entrada != NULL && $asistencias2[$empleado->id.'_'.$fecha]->salida != NULL){                    
						$carbon1 = new \Carbon\Carbon($asistencias2[$empleado->id.'_'.$fecha]->entrada);
						$carbon2 = new \Carbon\Carbon($asistencias2[$empleado->id.'_'.$fecha]->salida);
						$horas = $carbon1->diffInHours($carbon2);
					}
                    

                }else{
                    // Sn registro de biometrico
                    $entrada = 'SIN REGISTRO';
                    $salida = 'SIN REGISTRO';
                    $horas = 0;
                }

                
                $export[] = [
                    'id' => $empleado->id,
                    'num' => $empleado->numero_empleado,
                    'nombre' => $empleado->apaterno.' '.$empleado->amaterno.' '.$empleado->nombre,
                    'fecha' => $fecha,
                    'entrada' => $entrada,
                    'salida' => $salida,
                    'horas' => $horas,
                    
                ];
                $renglon++;
            }
            $export[] = ['','','','','','','','',''];
            $renglon++;
        }

        return collect($export);
    }

    public function headings(): array
    {
        return [
            'ID',
            'NUM EMPLEADO',
            'NOMBRE',
            'FECHA',
            'REG. ENTRADA',
            'REG. SALIDA',
            'HORAS TRABAJADAS'
        ];
    }


    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getDelegate()->getPageSetup()->setOrientation('landscape');
                $encabezados = 'A1:W1'; // All headers
                $event->sheet->getDelegate()->getStyle($encabezados)->getFont()->setSize(14);


                $styleArray = [
                    'borders' => [
                        'outline' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => 'FFFF0000'],
                        ],
                    ],
                ];

            },
        ];
    }
}
