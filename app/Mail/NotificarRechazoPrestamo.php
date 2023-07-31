<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificarRechazoPrestamo extends Mailable
{

    public $prestamoId;
    public $informacion;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($prestamoId, $informacion)
    {
        $this->prestamoId = $prestamoId;
        $this->informacion = $informacion;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.prestamos.notificar_empleado_rechazo');
    }
}
