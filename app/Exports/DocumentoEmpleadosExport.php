<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Events\AfterSheet;
use App\Models\Empleado;

class DocumentoEmpleadosExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents
{
    use Exportable, RegistersEventListeners;

    protected $ineS;
    protected $ineN;

    protected $curpS = [];
    protected $nssS = [];
    protected $nacimientoS = [];
    protected $comprobanteS = [];
    protected $avisoS = [];
    protected $estadoS = [];
    protected $contratoS = [];
    protected $rfcS = [];
    protected $fotoS = [];
    protected $analisisS = [];
    protected $fonacotS = [];
    protected $curriculumS = [];
    protected $acuseS = [];
    protected $estado_cuentaS = [];
    protected $firl_imssS = [];

    protected $curpN = [];
    protected $nssN = [];
    protected $nacimientoN = [];
    protected $comprobanteN = [];
    protected $avisoN = [];
    protected $estadoN = [];
    protected $contratoN = [];
    protected $rfcN = [];
    protected $fotoN = [];
    protected $analisisN = [];
    protected $fonacotN = [];
    protected $curriculumN = [];
    protected $acuseN = [];
    protected $estado_cuentaN = [];
    protected $fiel_imssN = [];

    public function __construct()
    {
    }

    public function collection()
    {
        $renglon = 2;
        cambiarBase(Session::get('base'));
        $empleados = array();

        $repositorio = 'storage/repositorio/' . Session::get('empresa')['id'];

        $emplea = Empleado::select(
            'id',
            'numero_empleado',
            'nombre',
            'apaterno',
            'amaterno',
            'repositorio',
            'file_ine',
            'file_curp',
            'file_nss',
            'file_nacimiento',
            'file_comprobante',
            'file_aviso',
            'file_estado',
            'file_contrato',
            'file_rfc',
            'file_fotografia',
            'file_analisis',
            'file_fonacot',
            'file_curriculum',
            'file_acuse',
            'file_estado_cuenta',
            'file_fiel_imss'
        )->where('estatus', [Empleado::EMPLEADO_ACTIVO])->get();

        foreach ($emplea as $empleado) {

            if ($empleado->file_ine != "" && file_exists($repositorio . '/' . $empleado->id . '/' . $empleado->file_ine)) {
                $ine  =  "TIENE";
                $this->ineS[] = $renglon;
            } else {
                $ine  =  "NO TIENE";
                $this->ineN[] = $renglon;
            }

            if ($empleado->file_curp != "" && file_exists($repositorio . '/' . $empleado->id . '/' . $empleado->file_curp)) {
                $curp = "TIENE";
                $this->curpS[] = $renglon;
            } else {
                $curp = "NO TIENE";
                $this->curpN[] = $renglon;
            }
            if ($empleado->file_nss != "" && file_exists($repositorio . '/' . $empleado->id . '/' . $empleado->file_nss)) {
                $nss  = "TIENE";
                $this->nssS[] = $renglon;
            } else {
                $nss  = "NO TIENE";
                $this->nssN[] = $renglon;
            }
            if ($empleado->file_nacimiento != "" && file_exists($repositorio . '/' . $empleado->id . '/' . $empleado->file_nacimiento)) {
                $nacimiento = "TIENE";
                $this->nacimientoS[] = $renglon;
            } else {
                $nacimiento = "NO TIENE";
                $this->nacimientoN[] = $renglon;
            }
            if ($empleado->file_comprobante != "" && file_exists($repositorio . '/' . $empleado->id . '/' . $empleado->file_comprobante)) {
                $comprobante =  "TIENE";
                $this->comprobanteS[] = $renglon;
            } else {
                $comprobante =  "NO TIENE";
                $this->comprobanteN[] = $renglon;
            }
            if ($empleado->file_aviso != "" && file_exists($repositorio . '/' . $empleado->id . '/' . $empleado->file_aviso)) {
                $aviso = "TIENE";
                $this->avisoS[] = $renglon;
            } else {
                $aviso = "NO TIENE";
                $this->avisoN[] = $renglon;
            }
            if ($empleado->file_estado != "" && file_exists($repositorio . '/' . $empleado->id . '/' . $empleado->file_estado)) {
                $estado = "TIENE";
                $this->estadoN[] = $renglon;
            } else {
                $estado = "NO TIENE";
                $this->estadoN[] = $renglon;
            }
            if ($empleado->file_contrato != "" && file_exists($repositorio . '/' . $empleado->id . '/' . $empleado->file_contrato)) {
                $contrato = "TIENE";
                $this->contratoS[] = $renglon;
            } else {
                $contrato = "NO TIENE";
                $this->contratoN[] = $renglon;
            }
            if ($empleado->file_rfc != "" && file_exists($repositorio . '/' . $empleado->id . '/' . $empleado->file_rfc)) {
                $rfc = "TIENE";
                $this->rfcS[] = $renglon;
            } else {
                $rfc = "NO TIENE";
                $this->rfcN[] = $renglon;
            }
            if ($empleado->file_fotografia != "" && file_exists($repositorio . '/' . $empleado->id . '/' . $empleado->file_fotografia)) {
                $foto = "TIENE";
                $this->fotoS[] = $renglon;
            } else {
                $foto = "NO TIENE";
                $this->fotoN[] = $renglon;
            }
            if ($empleado->file_analisis != "" && file_exists($repositorio . '/' . $empleado->id . '/' . $empleado->file_analisis)) {
                $analisis = "TIENE";
                $this->analisisS[] = $renglon;
            } else {
                $analisis = "NO TIENE";
                $this->analisisN[] = $renglon;
            }
            if ($empleado->file_fonacot != "" && file_exists($repositorio . '/' . $empleado->id . '/' . $empleado->file_fonacot)) {
                $fonacot = "TIENE";
                $this->fonacotS[] = $renglon;
            } else {
                $fonacot = "NO TIENE";
                $this->fonacotN[] = $renglon;
            }
            if ($empleado->file_curriculum != "" && file_exists($repositorio . '/' . $empleado->id . '/' . $empleado->file_curriculum)) {
                $curriculum = "TIENE";
                $this->curriculumS[] = $renglon;
            } else {
                $curriculum = "NO TIENE";
                $this->curriculumN[] = $renglon;
            }
            if ($empleado->file_acuse != "" && file_exists($repositorio . '/' . $empleado->id . '/' . $empleado->file_acuse)) {
                $acuse = "TIENE";
                $this->acuseS[] = $renglon;
            } else {
                $acuse = "NO TIENE";
                $this->acuseN[] = $renglon;
            }
            if ($empleado->file_estado_cuenta != "" && file_exists($repositorio . '/' . $empleado->id . '/' . $empleado->file_estado_cuenta)) {
                $estado_cuenta = "TIENE";
                $this->estado_cuentaS[] = $renglon;
            } else {
                $estado_cuenta = "NO TIENE";
                $this->estado_cuentaN[] = $renglon;
            }
            if ($empleado->file_fiel_imss != "" && file_exists($repositorio . '/' . $empleado->id . '/' . $empleado->file_fiel_imss)) {
                $fiel_imss = "TIENE";
                $this->fiel_imssS[] = $renglon;
            } else {
                $fiel_imss =  "NO TIENE";
                $this->fiel_imssN[] = $renglon;
            }

            $e = array(
                'id' => $empleado->id,
                'num_empleado' => $empleado->numero_empleado,
                'nombre' => $empleado->nombre . ' ' . $empleado->apaterno . ' ' . $empleado->amaterno,
                'ine' => $ine,
                'curp' => $curp,
                'nss' => $nss,
                'nacimiento' => $nacimiento,
                'comprobante' => $comprobante,
                'estado_cuenta' => $estado_cuenta,
                'contrato' => $contrato,
                'rfc' => $rfc,
                'foto' => $foto,
                'fonacot' => $fonacot,
                'curriculum' => $curriculum,
                'fiel_imss' => $fiel_imss
            );

            $empleados[] = $e;

            $renglon++;
        }
        return collect($empleados);
    }

    public function headings(): array
    {
        return [
            'ID',
            'NUM EMPLEADO',
            'NOMBRE',
            'INE',
            'CURP',
            'NSS',
            'ACTA DE NACIMIENTO',
            'COMPROBANTE DE DOMICILIO',
            'ESTADO DE CUENTA',
            'CONTRATO',
            'RFC',
            'FOTOGRAFIA',
            'FONACOT',
            'CURRICULUM',
            'FIEL_IMSS',

        ];
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->getPageSetup()->setOrientation('landscape');

                $encabezados = 'A1:O1';

                $styleArray = [
                    'borders' => [
                        'outline' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => '00000000']
                    ],
                    'font' => [
                        'color' => ['argb' => 'FFFFFFFF'],
                        'bold' => true,
                        'size' => 12
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ];

                $styleArrayR = [
                    'borders' => [
                        'outline' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => 'FFFF0000'],
                        ],
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFCC0000']
                    ],
                    'font' => [
                        'color' => ['argb' => 'FFFFFFFF'],
                        'bold' => true
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ];

                $styleArrayV = [
                    'borders' => [
                        'outline' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => 'FF00FF00'],
                        ],
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF007434']
                    ],
                    'font' => [
                        'color' => ['argb' => 'FFFFFFFF'],
                        'bold' => true
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ];
                $event->sheet->getDelegate()->getStyle($encabezados)->applyFromArray($styleArray);

                $event->sheet->freezePane('A2');
                /* INE */
                if (isset($this->ineS)) {
                    foreach ($this->ineS as $renglon) {
                        $event->sheet->getStyle('D' . $renglon)->applyFromArray($styleArrayV);
                    }
                }

                if (isset($this->ineN)) {
                    foreach ($this->ineN as $renglon) {
                        $event->sheet->getStyle('D' . $renglon)->applyFromArray($styleArrayR);
                    }
                }

                /* CURP */
                if(isset($this->curpS)){
                    foreach ($this->curpS as $renglon) {
                        $event->sheet->getStyle('E'.$renglon)->applyFromArray($styleArrayV);
                    }
                }

                if(isset($this->curpN)){
                    foreach ($this->curpN as $renglon) {
                        $event->sheet->getStyle('E'.$renglon)->applyFromArray($styleArrayR);
                    }
                }

                /* NSS */
                if (isset($this->nssS)) {
                    foreach ($this->nssS as $renglon) {
                        $event->sheet->getStyle('F' . $renglon)->applyFromArray($styleArrayR);
                    }
                }

                if (isset($this->nssN)) {
                    foreach ($this->nssN as $renglon) {
                        $event->sheet->getStyle('F' . $renglon)->applyFromArray($styleArrayR);
                    }
                }

                /* ACTA DE NACIMIENTO */
                if (isset($this->nacimientoS)) {
                    foreach ($this->nacimientoS as $renglon) {
                        $event->sheet->getStyle('G' . $renglon)->applyFromArray($styleArrayV);
                    }
                }

                if (isset($this->nacimientoN)) {
                    foreach ($this->nacimientoN as $renglon) {
                        $event->sheet->getStyle('G' . $renglon)->applyFromArray($styleArrayR);
                    }
                }

                /* COMPROBANTE DE DOMICILIO */
                if (isset($this->comprobanteS)) {
                    foreach ($this->comprobanteS as $renglon) {
                        $event->sheet->getStyle('H' . $renglon)->applyFromArray($styleArrayV);
                    }
                }

                if (isset($this->comprobanteN)) {
                    foreach ($this->comprobanteN as $renglon) {
                        $event->sheet->getStyle('H' . $renglon)->applyFromArray($styleArrayR);
                    }
                }

                /* ESTADO DE CUENTA */
                if (isset($this->estado_cuentaS)) {
                    foreach ($this->estado_cuentaS as $renglon) {
                        $event->sheet->getStyle('I' . $renglon)->applyFromArray($styleArrayV);
                    }
                }
                if (isset($this->estado_cuentaN)) {
                    foreach ($this->estado_cuentaN as $renglon) {
                        $event->sheet->getStyle('I' . $renglon)->applyFromArray($styleArrayR);
                    }
                }

                /* CONTRATO */
                if (isset($this->contratoS)) {
                    foreach ($this->contratoS as $renglon) {
                        $event->sheet->getStyle('J' . $renglon)->applyFromArray($styleArrayV);
                    }
                }

                if (isset($this->contratoN)) {
                    foreach ($this->contratoN as $renglon) {
                        $event->sheet->getStyle('J' . $renglon)->applyFromArray($styleArrayR);
                    }
                }

                /* RFC */
                if (isset($this->rfcS)) {
                    foreach ($this->rfcS as $renglon) {
                        $event->sheet->getStyle('K' . $renglon)->applyFromArray($styleArrayV);
                    }
                }
                if (isset($this->rfcN)) {
                    foreach ($this->rfcN as $renglon) {
                        $event->sheet->getStyle('K' . $renglon)->applyFromArray($styleArrayR);
                    }
                }

                /* FOTOGRAFIA */
                if (isset($this->fotoS)) {
                    foreach ($this->fotoS as $renglon) {
                        $event->sheet->getStyle('L' . $renglon)->applyFromArray($styleArrayV);
                    }
                }
                if (isset($this->fotoN)) {
                    foreach ($this->fotoN as $renglon) {
                        $event->sheet->getStyle('L' . $renglon)->applyFromArray($styleArrayR);
                    }
                }

                /* FONACOT */
                if (isset($this->fonacotS)) {
                    foreach ($this->fonacotS as $renglon) {
                        $event->sheet->getStyle('M' . $renglon)->applyFromArray($styleArrayV);
                    }
                }

                if (isset($this->fonacotN)) {
                    foreach ($this->fonacotN as $renglon) {
                        $event->sheet->getStyle('M' . $renglon)->applyFromArray($styleArrayR);
                    }
                }

                /* CURRICULUM */
                if (isset($this->curriculumS)) {
                    foreach ($this->curriculumS as $renglon) {
                        $event->sheet->getStyle('N' . $renglon)->applyFromArray($styleArrayV);
                    }
                }

                if (isset($this->curriculumN)) {
                    foreach ($this->curriculumN as $renglon) {
                        $event->sheet->getStyle('N' . $renglon)->applyFromArray($styleArrayR);
                    }
                }

                /* FIEL IMSS */
                if (isset($this->fiel_imssS)) {
                    foreach ($this->fiel_imssS as $renglon) {
                        $event->sheet->getStyle('O' . $renglon)->applyFromArray($styleArrayV);
                    }
                }

                if (isset($this->fiel_imssN)) {
                    foreach ($this->fiel_imssN as $renglon) {
                        $event->sheet->getStyle('O' . $renglon)->applyFromArray($styleArrayR);
                    }
                }
            }
        ];
    }
}
