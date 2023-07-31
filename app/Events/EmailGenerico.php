<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class EmailGenerico
{
    use Dispatchable, SerializesModels;

    public $para;
    public $titulo;
    public $cuerpo;
    public $btnTxt;
    public $btnUrl;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($para, $titulo, $cuerpo, $btnTxt, $btnUrl)
    {
        $this->para = $para;
        $this->titulo = $titulo;
        $this->cuerpo = $cuerpo;
        $this->btnTxt = $btnTxt;
        $this->btnUrl = $btnUrl;
    }
}
