<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NuevoReciboNominaEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $fecha_inicial_periodo;
    public $fecha_final_periodo;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($fecha_inicial_periodo, $fecha_final_periodo)
    {
        $this->fecha_inicial_periodo = $fecha_inicial_periodo;
        $this->fecha_final_periodo = $fecha_final_periodo;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {          
        return $this->subject('Nuevo Recibo de Nomina')->markdown('emails.empleado.nuevo-recibo-nomina');       
    }
}
