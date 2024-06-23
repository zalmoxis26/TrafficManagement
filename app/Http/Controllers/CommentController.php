<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\User;
use App\Models\Embarque;
use App\Events\NuevoComentario;
use App\Events\NuevoComentarioEmbarque;
use Illuminate\Support\Facades\Log;

class CommentController extends Controller
{
    public function agregarComentario(Request $request)
    {
        // Validamos los datos del formulario
        $request->validate([
            'content' => 'required|string',
            'trafico_id' => 'required|exists:traficos,id'
        ]);

        // Creamos el comentario
        $comentario = new Comment();
        $comentario->content = $request->content;
        $comentario->trafico_id = $request->trafico_id;
        $comentario->user_id = auth()->id();
        
    
        $comentario->save();
        // Obtenemos el nombre del usuario asociado al comentario
        $user = User::find($comentario->user_id);
        $userName = $user->name;


         // Disparamos el evento
        event(new NuevoComentario($comentario, $userName));

        // Respondemos con un JSON indicando el éxito y el comentario creado,
        // junto con el nombre del usuario asociado al comentario
        return response()->json([
            'success' => true,
            'comentario' => $comentario,
            'user_name' => $userName // Enviamos el nombre del usuario en la respuesta JSON
        ]);
    }

    public function agregarComentarioEmbarque(Request $request)
    {
        // Validamos los datos del formulario
        $request->validate([
            'content' => 'required|string',
            'embarque_id' => 'required'
        ]);

        // Creamos el comentario
        $comentario = new Comment();
        $comentario->content = $request->content;
        $comentario->embarque_id = $request->embarque_id;
        $comentario->user_id = auth()->id();
        
    
        $comentario->save();
        // Obtenemos el nombre del usuario asociado al comentario
        $user = User::find($comentario->user_id);
        $userName = $user->name;


        $Numembarque = Embarque::find($comentario->embarque_id);
        $embarque = $Numembarque;
       


         // Disparamos el evento
        event(new NuevoComentarioEmbarque($comentario, $userName, $embarque));

        // Respondemos con un JSON indicando el éxito y el comentario creado,
        // junto con el nombre del usuario asociado al comentario
        return response()->json([
            'success' => true,
            'comentario' => $comentario,
            'user_name' => $userName,
            'embarque' => $embarque,
    
        ]);
    }


}
