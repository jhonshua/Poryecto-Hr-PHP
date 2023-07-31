<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class EmpleadoCredencialesLogin extends Mailable
{
    use Queueable, SerializesModels;

    public $nombreEmpleado, $password, $emailEmpleado;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($emailEmpleado, $nombreEmpleado, $password)
    {
        $this->nombreEmpleado = $nombreEmpleado;
        $this->emailEmpleado = $emailEmpleado;
        $this->password = $password;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.empleado.empleadoCredencialesLogin');
    }
}
