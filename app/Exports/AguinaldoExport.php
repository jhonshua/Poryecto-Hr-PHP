<?php

namespace App\Exports;
use App\Models\Empleado;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;

class AguinaldoExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents
{
    use Exportable, RegistersEventListeners;
    protected $deptos;
    protected $ejercicio;
    public function __construct($ejercicio, $deptos = [])
    {
        $this->ejercicio = ($ejercicio > 0) ? $ejercicio : date('Y');
        $this->deptos    = $deptos;
        $this->sindical  = 0;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $export = [];
        cambiarBase(Session::get('base'));
        $deptos = $this->deptos;

        $this->sindical = DB::connection('empresa')->table('conceptos_nomina')->where('file_rool', 0)->where('estatus',1)->count();
        $aguinaldos = DB::connection('empresa')->table('aguinaldo')
                                    ->where('ejercicio', $this->ejercicio)
                                    ->whereIn('id_empleado', function($query) use($deptos){
                                        $query->select('id')
                                        ->from(with(new Empleado)->getTable())
                                        ->where('estatus', Empleado::EMPLEADO_ACTIVO)
                                        ->whereIn('id_departamento', $deptos);
                                    })->orderBy('id_empleado')->get()->keyBy('id_empleado');

        $empleados = Empleado::where('estatus', Empleado::EMPLEADO_ACTIVO)
                                ->whereIn('id', $aguinaldos->pluck('id_empleado')->toArray())
                                ->whereIn('id_departamento', $deptos)
                                ->orderBy('id')
                                ->get();
        
        foreach($empleados as $empleado){
            $empleado->aguinaldo = $aguinaldos[$empleado->id];            
            
            if($empleado->aguinaldo->impuestos<>0 || $empleado->aguinaldo->impuesto_anual<>0 || $empleado->aguinaldo->pension_alimenticia<>0 || $empleado->aguinaldo->descuentos_otros){
                $deducciones=$empleado->aguinaldo->impuestos + $empleado->aguinaldo->impuesto_anual;
            }else{
                $deducciones='0.0';
            }

            $aguinaldo =  [
                'id'                  => $empleado->id,
                'numero_empleado'     => ($empleado->numero_empleado) ?? $empleado->id,
                'nombre'              => $empleado->nombre_completo,
                'fecha_antiguedad'    => $empleado->fecha_antiguedad,
                'fecha_fiscal'         => $empleado->aguinaldo->fecha_fiscal,
                'dias'                => $empleado->aguinaldo->dias_aguinaldo,
                'dias_fiscales'        => $empleado->aguinaldo->dias_fiscales,
                'pago_aguinaldo'      => $empleado->aguinaldo->pago_aguinaldo,
                'total_percepciones'  => $empleado->aguinaldo->pago_aguinaldo,
                'impuesto_anual'      => $empleado->aguinaldo->impuesto_anual,
                'impuesto'            => $empleado->aguinaldo->impuestos,
                'pension_alimenticia' => $empleado->aguinaldo->pension_alimenticia,
                'descuentos_otros'    => $empleado->aguinaldo->descuentos_otros,
                'total_decucciones'   => $deducciones,
                'total'               => $empleado->aguinaldo->neto,
            ];

            if($this->sindical <= 0){
                $aguinaldo['pago_bono']             = $empleado->aguinaldo->importe2;
                $aguinaldo['s_pension_alimenticia'] = $empleado->aguinaldo->s_pension_alimenticia;
                $aguinaldo['s_descuentos_otros']    = $empleado->aguinaldo->s_descuentos_otros;
                $aguinaldo['neto2']                 = $empleado->aguinaldo->neto2;
            }

            $export[] = $aguinaldo;
        }

        return collect($export);
    }

    public function headings(): array
    {
        $headers =  [
            "ID",
            "NUMERO EMPLEADO",
            "NOMBRE",
            "FECHA ANTIGUEDAD",
            "FECHA FISCAL",
            "DIAS A PAGAR",
            "DIAS FISCALES",
            "AGUINALDO",
            "TOTAL PERCEPCIONES",
            "AJUSTE ANUAL",
            "ISR", 
            "PENSION ALIMENTICIA",
            "DESCUENTOS OTROS",
            "TOTAL DEDUCCIONES",
            "TOTAL", 
        ];

        if($this->sindical <= 0){
            $headers[] = "BONO AGUINALDO";
            $headers[] = "S PENSION ALIMENTICIA";
            $headers[] = "S DESCUENTOS OTROS";
            $headers[] = "TOTAL A PAGAR";
        }
        return $headers;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getDelegate()->getPageSetup()->setOrientation('landscape');
                $encabezados = 'A1:S1'; // All headers
                $event->sheet->getDelegate()->getStyle($encabezados)->getFont()->setSize(16); // Font

                $event->sheet->getDelegate()->getStyle('A1:'.$event->sheet->getDelegate()->getHighestColumn().'1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('f3c82e'); // Background
            },
        ];
    }
}
