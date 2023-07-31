<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\{Importable, ToCollection, WithHeadingRow, WithValidation, SkipsFailures};

class DiasFeriadosImport implements ToCollection, WithHeadingRow
{
    use Importable, SkipsFailures;
    protected $id_horario;

    public function  __construct($id_horario)
    {
        $this->id_horario = $id_horario;
    }


    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        cambiarBase(Session::get('base'));
        foreach ($rows as $row){
            // conversion de excel a date
            $fecha = gmdate("Y-m-d", ($row['fecha_festiva'] - 25569) * 86400);
            DB::connection('empresa')
                ->table('horarios_dias')
                ->insert(
                    [
                        'id_horario' => $this->id_horario,
                        'motivo' => strtoupper($row['motivo']),
                        'fecha_festiva' => $fecha,
                        'usuario_alta' => Auth::user()->email,
                    ]
                );
        }
    }
}
