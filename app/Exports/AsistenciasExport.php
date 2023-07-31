<?php

namespace App\Exports;

use \Maatwebsite\Excel\Sheet;
use App\Models\Empleado;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use App\Models\PeriodosNomina;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Events\AfterSheet;

class AsistenciasExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents
{
    use Exportable, RegistersEventListeners;


    protected $id_periodo;
    protected $dias_festivos;
    protected $inasistencias;
    protected $retardos;
    protected $noretardos;
    protected $nonoinasistencias;

    public function __construct($id_periodo )
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
        $periodo = PeriodosNomina::find($this->id_periodo);
        $fecha_inicio = $periodo->fecha_inicial_periodo;
        $fecha_fin = $periodo->fecha_final_periodo;

        $empleados = Empleado::where('estatus', [Empleado::EMPLEADO_ACTIVO])
                        ->where('id_horario', '<>', 0)
                        ->with('horario')
                        ->orderBy('apaterno', 'asc')
                        ->get()->keyBy('id');

        $asistencias = DB::connection('empresa')->table('asistencia_horario')
                            ->select('id_empleado','dia','comida', 'entrada', 'salida', 'asistencia', 'retardo', 'permiso')
                            ->whereBetween('dia', [$fecha_inicio, $fecha_fin])
                            ->orderBy('id_empleado')
                            ->get();
        
        foreach ($asistencias as $asistencia ) {
            $asistencias2[$asistencia->id_empleado.'_'.$asistencia->dia] = $asistencia;
        }
                 
        $this->dias_festivos = DB::connection('empresa')->table('horarios_dias')
                            ->select('fecha_festiva')
                            // ->where('id_horario', $empleado->id_horario)
                            ->whereBetween('fecha_festiva', [$fecha_inicio, $fecha_fin])
                            ->get()->keyBy('fecha_festiva');
        
        $fechas_a_mostrar = CarbonPeriod::create($fecha_inicio, $fecha_fin);

        foreach ($empleados as $empleado ) {

            foreach ($fechas_a_mostrar as $fecha1 ) {
                
                $fecha = $fecha1->format('Y-m-d');
                // dd($fecha);

                $dias_laborables = [
                    1 => $empleado->horario->lunes,
                    2 => $empleado->horario->martes,
                    3 => $empleado->horario->miercoles,
                    4 => $empleado->horario->jueves,
                    5 => $empleado->horario->viernes,
                    6 => $empleado->horario->sabado,
                    7 => $empleado->horario->domingo,
                ];

                // numero de la semana
                $num_dia = date('N', strtotime($fecha));

                $asistenciax = $retardo = $permiso = '';
                $incio_comida="---";
                $fin_comida ="---";
                $permiso = "";
                if( array_key_exists($empleado->id.'_'.$fecha, $asistencias2) ) {
                    // dd($empleado->id.'_'.$fecha, $asistencias2[$empleado->id.'_'.$fecha]->entrada);
                    $entrada = ($asistencias2[$empleado->id.'_'.$fecha]->entrada !="" && $asistencias2[$empleado->id.'_'.$fecha]->entrada !=NULL)?date('H:i:s', strtotime($asistencias2[$empleado->id.'_'.$fecha]->entrada)):"SIN REGISTRO";
                    $salida = ($asistencias2[$empleado->id.'_'.$fecha]->salida !="" && $asistencias2[$empleado->id.'_'.$fecha]->salida !=NULL && $asistencias2[$empleado->id.'_'.$fecha]->salida != $asistencias2[$empleado->id.'_'.$fecha]->entrada)?date('H:i:s', strtotime($asistencias2[$empleado->id.'_'.$fecha]->salida)):"SIN REGISTRO";

                    $asistenciax = ($asistencias2[$empleado->id.'_'.$fecha]->asistencia == 1) ? 'SI' : 'NO';
                    $retardo = ($asistencias2[$empleado->id.'_'.$fecha]->retardo == 1) ? 'SI' : 'NO';
                    if($asistencias2[$empleado->id.'_'.$fecha]->retardo == 1 || $asistencias2[$empleado->id.'_'.$fecha]->asistencia == 0){
                        $permiso = ($asistencias2[$empleado->id.'_'.$fecha]->permiso == 1) ? 'SI' : 'NO';
                    }
                   
                    if($asistencias2[$empleado->id.'_'.$fecha]->comida == 1){
                        // dd($asistencias2[$empleado->id.'_'.$fecha]->comida);
                        $incio_comida = ($asistencias2[$empleado->id.'_'.$fecha]->comida !="" && $asistencias2[$empleado->id.'_'.$fecha]->comida !=NULL)?date('H:i:s', strtotime($asistencias2[$empleado->id.'_'.$fecha]->comida) ):"---";
                        $comida   = ($asistencias2[$empleado->id.'_'.$fecha]->comida !="" && $asistencias2[$empleado->id.'_'.$fecha]->comida !=NULL && $asistencias2[$empleado->id.'_'.$fecha]->comida)?date('H:i:s', strtotime($asistencias2[$empleado->id.'_'.$fecha]->comida)):"---";
                    }
                    if ($asistencias2[$empleado->id.'_'.$fecha]->asistencia == 0) { $this->inasistencias[] = $renglon; }else{ $this->noinasistencias[] = $renglon; }

                    if ($asistencias2[$empleado->id.'_'.$fecha]->retardo == 1){ $this->retardos[] = $renglon; }else{ $this->noretardos[] = $renglon; }

                } else if($this->esFechaFestiva($empleado->id_horario, $fecha)){
                    // Si es dia festivo
                    $entrada = 'DÍA FERIADO O INHABIL';
                    $salida = 'DÍA FERIADO O INHABIL';

                } else if($dias_laborables[$num_dia] == 0){
                    // Dia no laborale estipulado en el horario
                    $entrada = 'DÍA NO LABORABLE';
                    $salida = 'DÍA NO LABORABLE';

                } else{
                    // Sn registro de biometrico
                    $entrada = 'SIN REGISTRO';
                    $salida = 'SIN REGISTRO';
                }

                
                $export[] = [
                    'id' => $empleado->id,
                    'num' => $empleado->numero_empleado,
                    'nombre' => $empleado->apaterno.' '.$empleado->amaterno.' '.$empleado->nombre,
                    'fecha' => $fecha,
                    'entrada' => $entrada,
                    'salida' => $salida,
                    'inicio comida' => $incio_comida,
                    'fin comida' => $fin_comida,
                    'asistencia' => $asistenciax,
                    'retardo' => $retardo,
                    'permiso' => $permiso,
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
            'INICIO COMIDA',
            'FIN COMIDA',
            'ASISTENCIA',
            'RETARDO',
            'PERMISO'
        ];
    }


    protected function esFechaFestiva($id_horario, $fecha)
    {
        return ($this->dias_festivos->where('id_horario', $id_horario)->where('fecha_festiva', $fecha)->count() > 0) ? true : false;
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
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFCC0000']
                    ],
                    'font' =>[
                        'color' => ['argb' => 'FFFFFFFF'],  
                        'bold'      =>  true                           
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ];
                
                $styleArray2 = [
                    'borders' => [
                        'outline' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => 'FF00FF00'],                            
                        ],
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF007434']
                    ],
                    'font' =>[
                        'color' => ['argb' => 'FFFFFFFF'],  
                        'bold'      =>  true                           
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ];

                $styleArray3 = [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ];
                // Marcamos las celdas con inasistencia
                if($this->inasistencias != null){
                    foreach ($this->inasistencias as $renglon) {
                        $event->sheet->getStyle('I'.$renglon)->applyFromArray($styleArray);
                    }                    
                }

                // Marcamos las celdas con inasistencia
                if($this->retardos != null){
                    foreach ($this->retardos as $renglon) {
                        $event->sheet->getStyle('J'.$renglon)->applyFromArray($styleArray);
                    }
                }

                if($this->noretardos != null){
                    foreach ($this->noretardos as $renglon) {
                        $event->sheet->getStyle('J'.$renglon)->applyFromArray($styleArray2);
                    }
                }
                
                if($this->nonoinasistencias != null){
                    foreach ($this->noinasistencias as $renglon) {
                        $event->sheet->getStyle('I'.$renglon)->applyFromArray($styleArray2);
                    }
                }

            },
        ];
    }
}
