<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{Importable, ToCollection, WithHeadingRow, SkipsFailures};
use App\Models\Empleado;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;


class CuentasBancariasImport implements ToCollection, WithHeadingRow
{
    use Importable, SkipsFailures;

    /**
     * @param Collection $collection
     */
    public function collection(Collection $rows)
    {
        cambiarBase(Session::get('base'));

        $bancos = DB::table('bancos')->get()->keyBy('banco1');
        $bancos = $this->flat2ItemCollection($bancos, 'banco1', 'nombre');

        $data_cuentas = [];

        foreach ($rows as $row) {

            $id_banco = array_search(strtoupper($row['banco']), $bancos);
 
               Empleado::where('id', $row['id'])->update([
                    'id_banco' => ($id_banco !== false) ? $id_banco : '',
                    'cuenta_bancaria' => $row['cuenta_bancaria_1'],
                    'cuenta_bancaria2' => $row['cuenta_bancaria_2'],
                    'cuenta_bancaria3' => $row['cuenta_bancaria_3'],
                    'clabe_interbancaria' => $row['clabe_interbancaria'],
                    'tipo_cuenta' => $row['tipo_cuenta'],
                    'id_bancario' => $row['id_bancario'],
                    ]
                
               );
        }
    }

    /**
     * Aplana un Collection para facilitar la busqueda de datos
     */
    private function flat2ItemCollection($collection, $key, $value)
    {
        $flaten = [];
        foreach ($collection as $item) {
            $flaten[$item->$key] = $item->$value;
        }
        return $flaten;
    }
}
