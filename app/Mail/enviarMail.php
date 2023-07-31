<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class enviarMail extends Mailable
{
    use Queueable, SerializesModels;

    public $titulo;
    public $cuerpo;
    public $btnTxt;
    public $btnUrl;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($titulo, $cuerpo, $btnUrl, $btnTxt)
    {
        $this->titulo = $titulo;
        $this->cuerpo = $cuerpo;
        $this->btnTxt = $btnTxt;
        $this->btnUrl = $btnUrl;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        
        return $this->subject($this->titulo)->markdown('emails.emailGenerico');
    }
}
