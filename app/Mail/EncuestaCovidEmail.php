<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EncuestaCovidEmail extends Mailable
{
    use Queueable, SerializesModels;
    public $titulo;
    public $cuerpo;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($titulo, $cuerpo)
    {
        $this->titulo = $titulo;
        $this->cuerpo = $cuerpo;
      
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->titulo)->markdown('emails.empleado.emailCovid');
    }
}
