<?php

namespace App\Imports;

use App\Models\Empleado;
use App\Models\Asistencia;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\{Importable, ToCollection, WithHeadingRow, WithValidation, SkipsFailures};


class importAsistencias implements ToCollection,WithHeadingRow
{

    use Importable, SkipsFailures;

    private $errores = 0;
    private $mensajeDeError = '';
    private $importados = 0;

    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        cambiarBase(Session::get('base'));
        foreach ($rows as $row){
           
            if($row->filter()->isNotEmpty()){
                Asistencia::create(
                    [
                        'id_empleado' => $row['id_empleado'],
                        'fecha' => $this->ExcelToPHPObject($row['fecha']),
                        'lugar' => $row['lugar']
                    ]
                );
                $this->importados++;
            }
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

     /**
     * Regresa los resultados de la importacion
     */
    public function getImportedResults()
    {
        return [
            'importados' => $this->importados,
            'errores' => $this->errores,
            'mensajeDeError' => $this->mensajeDeError
        ];
    }

    private function ExcelToPHP($dateValue = 0, $ExcelBaseDate = 1900) {
        if ($ExcelBaseDate == 1900) {
            $myExcelBaseDate = 25569;
            //    Adjust for the spurious 29-Feb-1900 (Day 60)
            if ($dateValue < 60) {
                --$myExcelBaseDate;
            }
        } else {
            $myExcelBaseDate = 24107;
        }
    
        // Perform conversion
        if ($dateValue >= 1) {
            $utcDays = $dateValue - $myExcelBaseDate;
            $returnValue = round($utcDays * 86400);
            if (($returnValue <= PHP_INT_MAX) && ($returnValue >= -PHP_INT_MAX)) {
                $returnValue = (integer) $returnValue;
            }
        } else {
            $hours = round($dateValue * 24);
            $mins = round($dateValue * 1440) - round($hours * 60);
            $secs = round($dateValue * 86400) - round($hours * 3600) - round($mins * 60);
            $returnValue = (integer) gmmktime($hours, $mins, $secs);
        }
    
        // Return
        return $returnValue;
    }    //    function ExcelToPHP()
    
    private function ExcelToPHPObject($dateValue = 0) {
        $dateTime = $this->ExcelToPHP($dateValue);
        $days = floor($dateTime / 86400);
        $time = round((($dateTime / 86400) - $days) * 86400);
        $hours = round($time / 3600);
        $minutes = round($time / 60) - ($hours * 60);
        $seconds = round($time) - ($hours * 3600) - ($minutes * 60);
    
        $dateObj = date_create('1-Jan-1970+'.$days.' days');
        $dateObj->setTime($hours,$minutes,$seconds);
    
        return $dateObj;
    }    //    function ExcelToPHPObject()
}
