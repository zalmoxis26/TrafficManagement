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
            <div class="card-body" style="overflow: auto; max-height:300px;" >
                <table class="table text-center">
                    <thead>
                        <tr>
                            <th>#TRAFICO</th>
                            <th>Fecha de Registro</th>
                            <th>Anexos</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                        $traficos = $embarque->traficos->sortBy('id'); // Puedes usar sortByDesc('id') para orden descendente
                    @endphp
                    
                    @foreach($traficos as $trafico)
                    <tr>
                        <td>{{ $trafico->id }}</td>
                        <td>{{ $trafico->fechaReg }}</td>
                        <td><a href="{{ route('traficos.show', $trafico->id) }}" class="btn btn-primary">Ver Anexos</a></td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>                 
           
        </div>
        <div class="card-footer text-muted"></div>
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
    
    <!--formatear links -->

    <script>
        $(document).ready(function() {
        function createLink(content) {
            // Expresión regular para detectar enlaces con http, https y www
            var urlPattern = /((https?:\/\/|www\.)[^\s]+)/g;
            return content.replace(urlPattern, function(url) {
                // Si el URL empieza con "www", añadimos "http://"
                var hyperlink = url.startsWith('www.') ? 'http://' + url : url;
                return '<a href="' + hyperlink + '" target="_blank">' + url + '</a>';
            });
        }

        function agregarComentario(content, userName, createdAt) {
            var formattedContent = createLink(content);
            var comentarioHtml = `
                <div class="comment">
                    <strong style="color:cornflowerblue;">[${userName}] ${createdAt}:</strong> ${formattedContent}
                </div>`;
            document.getElementById('comments').innerHTML += comentarioHtml;
        }

        // Inicializa el formateo para los comentarios existentes
        $('#comments .comment').each(function() {
            var $this = $(this);
            var formattedContent = createLink($this.html());
            $this.html(formattedContent);
        });

        // Exponer las funciones globalmente
        window.createLink = createLink;
        window.agregarComentario = agregarComentario;
    });
    </script>



<script>
    
    document.addEventListener('DOMContentLoaded', function() {
        // Obtener el valor inicial del número de embarque al cargar la página
        var initialNumEmbarque = $('#num_embarque').val();

        $('#num_embarque').on('change', function() {
            var numEmbarque = $(this).val();

            if (numEmbarque.trim() !== '' && numEmbarque !== initialNumEmbarque) {
                $.ajax({
                    url: "{{ route('validate.numEmbarque') }}",
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        numEmbarque: numEmbarque
                    },
                    success: function(response) {
                        if (response.exists) {
                            $('#numEmbarqueError').text('El número de embarque ya está en uso.').show();
                            $('#num_embarque').val('');
                        } else {
                            $('#numEmbarqueError').text('').hide();
                        }
                    }
                });
            } else {
                $('#numEmbarqueError').text('').hide();
            }
        });
    });
</script>


    

@endsection



