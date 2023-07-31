<?php

namespace App\Exports;

use App\Models\Prestacion;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;


class exportPrestaciones implements FromCollection, WithHeadings
{
    use Exportable;
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $id_categoria;

    public function __construct($id_categoria = null)
    {
        $this->id_categoria = $id_categoria;
    }

    public function collection()
    {
        cambiarBase(Session::get('base'));
        $prestaciones =  Prestacion::select('antiguedad', 'vacaciones', 'prima_vacacional', 'aguinaldo', 'factor_integracion', 'bono_aguinaldo', 'bono_vacaciones', 'bono_prima_vacacional')
            ->where('id_categoria', $this->id_categoria)
            ->where('estatus', 1)
            ->orderBy('antiguedad', 'asc')
            ->get();

        return $prestaciones;
    }

    public function headings(): array
    {
        return [
            'años_antiguedad',
            'dias_vacaciones',
            'dias_prima_vacacional',
            'dias_aguinaldo',
            'factor_integracion',
            'dias_bono_aguinaldo',
            'dias_bono_vacaciones',
            'dias_bono_prima_vacacional'
        ];
    }
}
