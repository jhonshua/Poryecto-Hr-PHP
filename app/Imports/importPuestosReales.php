<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\PuestoAlias;

class importPuestosReales implements ToModel,WithHeadingRow
{
    /**
    * @param array $collection
    */
    public function model(array $row)
    {
        cambiarBase(Session::get('base'));
        return new PuestoAlias(['alias'=>$row['puestos']]);
    }
}
