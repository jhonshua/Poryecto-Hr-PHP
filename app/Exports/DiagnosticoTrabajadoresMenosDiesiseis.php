<?php

namespace App\Exports;
use App\Models\BloqueCuestionario;
use App\Models\Actividad;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class DiagnosticoTrabajadoresMenosDiesiseis implements FromCollection, WithHeadings, ShouldAutoSize
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

        $bloques = BloqueCuestionario::where('idcuestionario','=',1)->with('preguntas')->get();
        foreach($bloques as $b){
            foreach($b->preguntas as $pregunta){
                array_push($cab,$pregunta->pregunta);
                //$preguntas[$pregunta->id] = $pregunta->pregunta;
            }
        }


     /*   if(count($this->head) > 0){
            array_push($cab,"Guia II");
            foreach($this->head as $val){
                array_push($cab,$val);
            }
            
        }*/
        return $cab;
    }
}
