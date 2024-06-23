<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Comment;


class NuevoComentarioEmbarque implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $comentario;
    public $userName;
    public $embarque;
    

    public function __construct(Comment $comentario, $userName, $embarque)
    
    {

        if (!$embarque->relationLoaded('traficos')) {
            $embarque->load('traficos');
        }

        $this->comentario = $comentario;
        $this->userName = $userName;
        $this->embarque = $embarque;
      

    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        return new Channel('comentarios-embarque');
    }

    public function broadcastWith()
    {
        return [
            'comentario' => $this->comentario,
            'user_name' => $this->userName,
            'embarque' => $this->embarque,
        
        ];
    }


}
