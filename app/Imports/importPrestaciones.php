<?php

namespace App\Imports;

use App\Models\Prestacion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\{Importable, ToCollection, WithHeadingRow, WithValidation, SkipsFailures};

class importPrestaciones implements ToCollection, WithHeadingRow
{
    use Importable, SkipsFailures;
    protected $id_categoria;

    public function  __construct($id_categoria)
    {
        $this->id_categoria = $id_categoria;
    }
    
    public function collection(Collection $rows)
    {
        cambiarBase(Session::get('base'));
       
        foreach ($rows as $row){
            // calculo de factor de integracion
            $porcentaje = $row['dias_prima_vacacional'] / 100;
            $factor1 = $row['dias_vacaciones'] * $porcentaje;
            $b = $row['dias_aguinaldo'] + 365;
            $facInt = ($factor1 + $b) / 365;

            Prestacion::updateOrCreate([
                'id_categoria' => $this->id_categoria,
                'antiguedad' => intval($row['anos_antiguedad']),
            ],[
                'vacaciones' => intval($row['dias_vacaciones']),
                'prima_vacacional' => intval($row['dias_prima_vacacional']),
                'aguinaldo' => intval($row['dias_aguinaldo']),
                'bono_aguinaldo' => intval($row['dias_bono_aguinaldo']),
                'bono_vacaciones' => intval($row['dias_bono_vacaciones']),
                'bono_prima_vacacional' => intval($row['dias_bono_prima_vacacional']),
                'factor_integracion' => $facInt,
                'estatus' => 1,
                'fecha_creacion' => date('Y-m-d H:i:s'),
                'fecha_edicion' => date('Y-m-d H:i:s')
            ]);
        }
    }
}
