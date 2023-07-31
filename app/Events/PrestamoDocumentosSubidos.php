<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class PrestamoDocumentosSubidos
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $prestamo_id;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($prestamo_id)
    {
        $this->prestamo_id = $prestamo_id;
    }
}
