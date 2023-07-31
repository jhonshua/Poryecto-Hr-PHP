<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class AvisoSistema
{
    use Dispatchable, SerializesModels;

    public $textoMail;
    public $destinatarios;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($destinatarios, $txt)
    {
        $this->destinatarios = $destinatarios;
        $this->textoMail = $txt;
    }
}
