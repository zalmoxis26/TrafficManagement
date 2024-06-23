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

class NuevoComentario implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $comentario;
    public $userName;

    public function __construct(Comment $comentario, $userName)
    {
          // Cargar la relación empresa si no está cargada
          if (!$comentario->relationLoaded('trafico')) {
            $comentario->load('trafico');
        }

        $this->comentario = $comentario;
        $this->userName = $userName;
    }


    public function broadcastOn()
    {
        return new Channel('comentarios-trafico');
       
    }

    public function broadcastWith()
    {
        return [
            'comentario' => $this->comentario,
            'user_name' => $this->userName,
        ];
    }

}
