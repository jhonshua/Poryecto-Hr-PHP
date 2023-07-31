<?php

namespace App\Exports;

use App\Models\Actividad;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use Maatwebsite\Excel\Concerns\ToModel;


use Maatwebsite\Excel\Concerns\WithMapping;

class DispersionXlsSinCabecera extends DefaultValueBinder  implements FromCollection, WithColumnFormatting, WithCustomValueBinder
{
    use Exportable;
    protected $empleados;
    protected $strictTextColumn = ['I'];

    public function bindValue(Cell $cell, $value)
    {
        if (is_numeric($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        // else return default behavior
        return parent::bindValue($cell, $value);
    }

    public function __construct($empleados = null)
    {
        $this->empleados = $empleados;
    }

    

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {   
        //dd($this->empleados);
       /* foreach($this->empleados as $empleado){
            $empleado['cuentaclabe'] = strval($empleado['cuentaclabe']);
        }*/
        return $this->empleados;
    }


    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_NUMBER,
            'D' => NumberFormat::FORMAT_NUMBER,
            'I' => NumberFormat::FORMAT_NUMBER,
        ];
    }
}
