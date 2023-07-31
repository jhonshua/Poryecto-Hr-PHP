<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use App\Models\Actividad;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use Maatwebsite\Excel\Concerns\ToModel;

class DispersionXlsBanorte extends DefaultValueBinder implements FromCollection, WithColumnFormatting, WithCustomValueBinder
{
    use Exportable;

    protected $empleados;
    public function __construct($empleados = null)
    {
        $this->empleados = $empleados;
    }

    public function bindValue(Cell $cell, $value)
    {
        if (is_numeric($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        // else return default behavior
        return parent::bindValue($cell, $value);
    }
    
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {   
        //dd($this->empleados);
        return $this->empleados;
    }

   /* public function headings(): array
    {
        return [
            'Oper',
            'Clave ID',
            'Cuenta Origen',
            'Cuenta/CLABE destino',
            'Importe',
            'Referencia',
            'Descripcion',
            'RFC Ordenante',
            'IVA',
            'Fecha aplicacion',
            'instruccion de pago'
        ];
    }*/
    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_NUMBER,
            'D' => NumberFormat::FORMAT_NUMBER,
            'E' => NumberFormat::FORMAT_NUMBER_00,
        ];
    }
}
