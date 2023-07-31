<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class PrestamoCreado
{
    use Dispatchable,  SerializesModels;

    public $prestamo;
    public $request;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($prestamo, $request)
    {
        $this->prestamo = $prestamo;
        $this->request = $request;
    }

}
