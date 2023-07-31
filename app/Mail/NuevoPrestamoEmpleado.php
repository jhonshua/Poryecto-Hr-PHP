<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\PrestamosTipos;

class NuevoPrestamoEmpleado extends Mailable
{
    use Queueable, SerializesModels;
    public $prestamoData;
    public $prestamo_seleccionado;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($prestamoData)
    {
        $this->prestamoData = $prestamoData;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->prestamo_seleccionado = PrestamosTipos::with('requisitos')
            ->whereIn('id', [$this->prestamoData->request['prestamos_tipo_id']])
            ->first();
        return $this->markdown('emails.prestamos.nuevo_prestamo_empleado');
    }
}
