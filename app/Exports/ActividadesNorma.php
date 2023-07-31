<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use App\Models\Actividad;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\Session;

class ActividadesNorma implements FromCollection, WithHeadings
{
    use Exportable;

    protected $id_implementacion;
    public function __construct($id_implementacion = null)
    {
        $this->id_implementacion = $id_implementacion;
    }

    /**
     * @return \Illuminate\Support\Collection
     */

    public function collection()
    {
        cambiarBase(Session::get('base'));
        $actividades = Actividad::select('descripcion', 'fecha_inicio', 'fecha_fin', 'notificacion', 'apertura_formulario')
            ->where('estatus', 1)
            ->where('idperiodo_implementacion', $this->id_implementacion)
            ->orderBy('fecha_inicio', 'asc')->get();

        foreach ($actividades as $actividad) {
            if ($actividad->notificacion == 1) {
                $actividad->notificacion = 'SI';
            } else {
                $actividad->notificacion = 'NO';
            }

            if ($actividad->apertura_formulario != "") {
                $actividad->apertura_formulario = 'SI';
            } else {
                $actividad->apertura_formulario = 'NO';
            }
        }
        return $actividades;
    }

    public function headings(): array
    {
        return [
            'Descripción',
            'Fecha inicio',
            'Fecha fin',
            'Notificación',
            'Apertura de formulario',
        ];
    }
}
