<?php

namespace App\Events;

use app\Models\Trafico;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TraficoCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $trafico;

    /**
     * Create a new event instance.
     */
    public function __construct(Trafico $trafico)
    {

         // Cargar la relación empresa si no está cargada
         if (!$trafico->relationLoaded('empresa')) {
            $trafico->load('empresa');
        }

        $this->trafico = $trafico;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        return new Channel('New-trafico');
    }

    public function broadcastWith(){

        return [
            'trafico' => $this->trafico->toArray(),
        ];

    }

}
