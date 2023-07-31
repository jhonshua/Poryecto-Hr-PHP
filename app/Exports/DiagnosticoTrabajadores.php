<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Models\Actividad;

class DiagnosticoTrabajadores implements FromCollection, WithHeadings, ShouldAutoSize
{
    use Exportable;

    protected $diagnosticos;
    protected $head;

    public function __construct($diagnosticos = null,$head = null  )
    {
        $this->diagnosticos = $diagnosticos;
        $this->head = $head;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return collect($this->diagnosticos);
    }

    public function headings(): array
    {
        $cab =  ['ID','Nombre','Guia I',];

        if(count($this->head) > 0){
            array_push($cab,"Guia II");
            foreach($this->head as $val){
                array_push($cab,$val);
            }
            
        }
        return $cab;
    }
}
