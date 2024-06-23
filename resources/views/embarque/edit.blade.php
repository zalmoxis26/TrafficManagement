@extends('layouts.app')

@section('template_title')
    {{ __('Update') }} Embarque
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="">
            <div class="col-md-8 mx-auto">

                <div class="card card-default">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float">
                            <span class="card-title">{{ __('Actualizar') }} Embarque</span>
                        </div>            
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('traficos.index') }}"> {{ __('Volver') }}</a>
                        </div>
                    </div>
                    <div class="card-body bg-white">
                        <form method="POST" action="{{ route('embarques.update', $embarque->id) }}"  role="form" enctype="multipart/form-data">
                            {{ method_field('PATCH') }}
                            @csrf

                            @include('embarque.form')

                        </form>
                    </div>
                </div>

            </div>
            
        </div>
        
    <div class="row">
        <div class="col-8 mx-auto">
        <div class="card mt-2">
            <div class="card-header">Traficos relacionados para embarque</div>
            <div class="card-body">
                <table class="table text-center">
                    <thead>
                        <tr>
                            <th>#TRAFICO</th>
                            <th>Fecha de Registro</th>
                            <th>Anexos</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($embarque->traficos as $trafico)
                        <tr>
                            <td>{{ $trafico->id }}</td>
                            <td>{{ $trafico->fechaReg }}</td>
                            <td><a href="{{ route('traficos.show', $trafico->id) }}" class="btn btn-primary">Ver Anexos</a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>                 
            <div class="card-footer text-muted"></div>
        </div>
        </div>
    </div>
</div>

        <div class="row mt-3 mx-auto">   
            <div class="col-8 mx-auto">     
                <div class="card">
                    <div class="card-header">CHAT DEL EMBARQUE</div>
                    <div class="card-body">
                        <div id="comments" style="height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;">
                            @if($embarque->comments->isNotEmpty() )
                                @foreach($embarque->comments as $comment)
                                    <div class="comment">
                                        <strong style="color:cornflowerblue;">[{{ $comment->user->name }}] {{ \Carbon\Carbon::parse($comment->created_at)->format('n/j/Y, g:i:s A') }}:</strong> {{ $comment->content }}
                                    </div>
                                @endforeach
                            @else
                                    <p class="no-comments-message">SIN COMENTARIOS PARA ESTE EMBARQUE </p>
                            @endif
                          </div>
                    </div>
                    <div class="card-footer text-muted">
                        <form action="{{ route('comentario.agregarEmbarque') }}" method="POST" id="comment-form" style="margin-top: 10px;">
                            @csrf
                            <textarea name="content" id="content" cols="30" rows="2" class="form-control" placeholder="Escribe tu comentario..."></textarea>
                            <input type="hidden" name="embarque_id" id="embarque_id" value="{{ $embarque->id }}">
                            <button type="button" class="btn btn-primary mt-2 float-end" id="enviar-comentario" >Enviar</button>
                        </form>
                    </div>
                </div>
            </div>    
        </div> 
    </section>

    <script>
    
        document.addEventListener('DOMContentLoaded', function() {    
            document.getElementById('enviar-comentario').addEventListener('click', function() {
                var formData = new FormData(document.getElementById('comment-form'));
                fetch("{{ route('comentario.agregarEmbarque') }}", {
                    method: "POST",
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Ocurrió un problema al enviar la solicitud.');
                    }
                    return response.json(); // Asegúrate de retornar los datos aquí
                })
                .then(data => {
                    if (data.success) {
                       
                        document.getElementById('content').value = ''; // Limpiamos el campo de texto
                       
                    } else {
                        console.error('Error al agregar el comentario');
                    }
                })
                .catch(error => {
                    console.error('Error al enviar la solicitud AJAX:', error);
                });
            });
        });
    </script>
    

@endsection



