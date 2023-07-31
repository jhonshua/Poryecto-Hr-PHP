<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificarEmpleadoDocumentoPrestamo extends Mailable
{
    use Queueable, SerializesModels;

    public $prestamoId;
    public $nombreEmpleado;
    public $tipoPrestamo;
    public $mensaje;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($prestamoId, $nombreEmpleado, $tipoPrestamo, $mensaje)
    {
        $this->prestamoId = encrypt($prestamoId);
        $this->nombreEmpleado = $nombreEmpleado;
        $this->tipoPrestamo = $tipoPrestamo;
        $this->mensaje = $mensaje;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.prestamos.notificar_empleado_documento_faltante');
    }
}
