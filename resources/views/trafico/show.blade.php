@extends('layouts.app')

<style>
    .fondo-verde {
            background-color: #c8e6c9 !important; /* Verde claro con !important */
          
        }


</style>    



@section('template_title')
    {{ $trafico->name ?? __('Show') . " " . __('Trafico') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-10 mx-auto">
                <div class="card" >
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">Anexos para Trafico #{{$trafico->id}}</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="javascript:history.back()"> {{ __('Back') }}</a>
                        </div>
                    </div>
                    <div class="card-body" style="max-height: 400px; overflow: auto;" >
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>    
                                        <th width="20%;" class="text-center">Asunto</th>
                                        <th width="30%;">Archivo</th>
                                        <th>Comentarios u Observaciones</th>
                                        <th>Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($trafico->anexos as $anexo)
                                        <tr>
                                            <td class="text-center">{{ $anexo->asunto }}</td>
                                            <td>
                                                <a href="{{ asset('storage/' . $anexo->archivo) }}" target="_blank">{{ basename($anexo->archivo) }}</a>
                                            </td>
                                            <td>{{ $anexo->descripcion }}</td>
                                            <td>{{ \Carbon\Carbon::parse($anexo->created_at)->format('d-M-Y H:i:s') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-3">
                        <div class="card">
                            <div class="card-header">Factura Vigente</div>
                            <div class="card-body fs-5">
                                <a data-bs-toggle="modal" data-bs-target="#facturaModal" href="" onclick="loadFactura('{{ $trafico->adjuntoFactura }}')">{{ $trafico->factura }} </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-3">
                        <div class="card">
                            <div class="card-header">Pedimento</div>
                            <div class="card-body">
                                @if($trafico->pedimento && $trafico->pedimento->adjunto)
                                <a class="text-success fs-5" data-bs-toggle="modal" data-bs-target="#pedimentoModal" href="" 
                                   onclick="loadPedimento('PedimentoTrafico_{{$trafico->id}}/{{ $trafico->pedimento->adjunto }}')"  
                                   title="Ver documento">
                                   {{$trafico->pedimento->numPedimento}}<i class="bi bi-file-earmark-ppt-fill"></i>
                                </a>
                            @else
                                <span class="text-muted fs-5"><i class="bi bi-file-earmark-ppt-fill"></i> Documento (No disponible)</span>
                            @endif                            
                            </div>
                        </div>
                    </div>

                    <div class="col-3">
                        <div class="card">
                            <div class="card-header">Revision Vigente</div>
                            <div class="card-body fs-5">
                                @if ($trafico->revision && $trafico->revision->adjuntoRevision)
                                    <a class="fs-5 text-dark" data-bs-toggle="modal" href="" data-bs-target="#documentModal" 
                                    onclick="loadDocument('{{ $trafico->revision->adjuntoRevision }}')">
                                    Revision <i class="bi bi-eye-fill"></i>
                                    </a>
                                @else
                                    <span class="fs-5 text-muted">Revision <i class="bi bi-eye-fill"></i> (No disponible)</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="card">
                            <div class="card-header">Historial del Trafico</div>
                            <div class="card-body fs-5">
                                @if ($trafico->historials->count() > 0)
                                    <a class="fs-5 text-dark" data-bs-toggle="modal" href="#historialsModal">
                                        Historial <i class="bi bi-eye-fill"></i>
                                    </a>
                                @else
                                    <span class="fs-5 text-muted"> Historial <i class="bi bi-eye-fill"></i> (No disponible)</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>    

                <div class="row mt-3">   
                    <div class="col-12">     
                        <div class="card">
                            <div class="card-header">CHAT DEL TRAFICO</div>
                            <div class="card-body">
                                <div id="comments" style="height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;">
                                    @if($trafico->comments->isNotEmpty() )
                                        @foreach($trafico->comments as $comment)
                                            <div class="comment">
                                                <strong style="color:cornflowerblue;">[{{ $comment->user->name }}] {{ \Carbon\Carbon::parse($comment->created_at)->format('n/j/Y, g:i:s A') }}:</strong> {{ $comment->content }}
                                            </div>
                                        @endforeach
                                    @else
                                            <p class="no-comments-message">SIN COMENTARIOS PARA ESTE TRAFICO </p>
                                    @endif
                                  </div>
                            </div>
                            <div class="card-footer text-muted">
                                <form action="{{ route('comentario.agregar') }}" method="POST" id="comment-form" style="margin-top: 10px;">
                                    @csrf
                                    <textarea name="content" id="content" cols="30" rows="2" class="form-control" placeholder="Escribe tu comentario..."></textarea>
                                    <input type="hidden" name="trafico_id" id="trafico_id" value="{{ $trafico->id }}">
                                    <button type="button" class="btn btn-primary mt-2 float-end" id="enviar-comentario" >Enviar</button>
                                </form>
                            </div>
                        </div>
                    </div>    
                </div> 

        </div>
    </section>

    <!-- SCRTIP QUE MANEJA EL ENVIO DE FORMULARIO DE COMENTARIOS EN TIEMPO REAL-->
<script>
    document.getElementById('enviar-comentario').addEventListener('click', function() {
      var formData = new FormData(document.getElementById('comment-form'));
      fetch("{{ route('comentario.agregar') }}", {
          method: "POST",
          body: formData,
          headers: {
              'X-CSRF-TOKEN': '{{ csrf_token() }}'
          }
      })
      .then(response => response.ok ? response.json() : Promise.reject('Ocurrió un problema al enviar la solicitud.'))
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
  
  </script>
  
  


@endsection


<!-- Modal HISTORIAL-->
<div class="modal fade" id="historialsModal" tabindex="-1" aria-labelledby="historialsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white" >
                <h5 class="modal-title" id="historialsModalLabel">Historial del Trafico</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="overflow: auto; max-height:500px;" >
                @if ($trafico->historials->count() > 0)
                    <ul class="list-group">
                     
                    @foreach($trafico->historials as $historial)

                @php
                    
                    $tieneAdjuntoValido = !is_null($historial->adjunto) && Storage::disk("public")->exists($historial->adjunto);
                
            
                @endphp
                        <li class="list-group-item @if($tieneAdjuntoValido) fondo-verde @endif">
                        @if(\Str::startsWith(strtolower(trim($historial->nombre)), 'recepcion') && $tieneAdjuntoValido)
                            <a href="{{ Storage::url($historial->adjunto) }}" target="_blank" rel="noopener noreferrer" style="text-decoration:none;color:black;">
                                <strong>{{ $historial->nombre }}</strong><br>
                                <span>{{ $historial->descripcion }}</span><br>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($historial->hora)->format('d-M-Y H:i:s') }}</small>
                              </a>
                            @else
                                <strong>{{ $historial->nombre }}</strong><br>
                                <span>{{ $historial->descripcion }}</span><br>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($historial->hora)->format('d-M-Y H:i:s') }}</small>
                            @endif         
                        </li>

                    @endforeach
                    </ul>
                @else
                    <p class="text-muted">No hay historiales disponibles.</p>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>



<!-- Modal REVISION-->
<div class="modal fade" id="documentModal" tabindex="-1" aria-labelledby="documentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="documentModalLabel">Vista Previa del Documento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <iframe id="documentIframe" frameborder="0" style="width: 100%; height: 500px; display: none;"></iframe>
                <a id="downloadLink" href="#" class="btn btn-primary" style="display: none;" download>Descargar Archivo</a>
            </div>
        </div>
    </div>
</div>

<!-- Modal PEDIMENTO-->
<div class="modal fade" id="pedimentoModal" tabindex="-1" aria-labelledby="pedimentoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="pedimentoModalLabel">Vista Previa del Documento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <iframe id="pedimentoIframe" frameborder="0" style="width: 100%; height: 500px; display: none;"></iframe>
                <a id="pedimentoDownloadLink" href="#" class="btn btn-primary" style="display: none;" download>Descargar Archivo</a>
            </div>
        </div>
    </div>
</div>

<!-- Modal FACTURA-->
<div class="modal fade" id="facturaModal" tabindex="-1" aria-labelledby="facturaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="facturaModalLabel">Vista Previa del Documento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <iframe id="facturaIframe" frameborder="0" style="width: 100%; height: 500px; display: none;"></iframe>
                <a id="facturaDownloadLink" href="#" class="btn btn-primary" style="display: none;" download>Descargar Archivo</a>
            </div>
        </div>
    </div>
</div>






<!-- LOAD REVISION -->

<script>
    function loadDocument(fileName) {
        let fileUrl = `{{ url('storage/') }}/${fileName}`;
        let extension = fileName.split('.').pop().toLowerCase();

        
        
        let documentIframe = document.getElementById('documentIframe');
        let downloadLink = document.getElementById('downloadLink');
        
        if (extension === 'xlsx' || extension === 'xls' || extension === 'doc' || extension === 'docx') {
            // If the file is an Excel file or a Word document, hide the iframe and show the download link
            documentIframe.style.display = 'none';
            downloadLink.href = fileUrl;
            downloadLink.style.display = 'block';
        } else {
            // For images and other file types, show the iframe and hide the download link
            documentIframe.src = fileUrl;
            documentIframe.style.display = 'block';
            downloadLink.style.display = 'none';
        }
    }
</script>



<!--LOAD PEDIMENTO -->

<script>
    function loadPedimento(fileName) {
        let fileUrl = `{{ url('storage/Pedimentos/') }}/${fileName}`;
        let extension = fileName.split('.').pop().toLowerCase();
        
        let documentIframe = document.getElementById('pedimentoIframe');
        let downloadLink = document.getElementById('pedimentoDownloadLink');

        
        if (extension === 'xlsx' || extension === 'xls' || extension === 'doc' || extension === 'docx') {
            // If the file is an Excel file or a Word document, hide the iframe and show the download link
            documentIframe.style.display = 'none';
            downloadLink.href = fileUrl;
            downloadLink.style.display = 'block';
        } else {
            // For images and other file types, show the iframe and hide the download link
            documentIframe.src = fileUrl;
            documentIframe.style.display = 'block';
            downloadLink.style.display = 'none';
        }
    }
</script>


<!-- LOAD FACTURA -->

<script>
    function loadFactura(fileName) {
        let fileUrl = `{{ url('storage') }}${fileName}`;
        let extension = fileName.split('.').pop().toLowerCase();
        
        let documentIframe = document.getElementById('facturaIframe');
        let downloadLink = document.getElementById('facturaDownloadLink');

        
        if (extension === 'xlsx' || extension === 'xls' || extension === 'doc' || extension === 'docx') {
            // If the file is an Excel file or a Word document, hide the iframe and show the download link
            documentIframe.style.display = 'none';
            downloadLink.href = fileUrl;
            downloadLink.style.display = 'block';
        } else {
            // For images and other file types, show the iframe and hide the download link
            documentIframe.src = fileUrl;
            documentIframe.style.display = 'block';
            downloadLink.style.display = 'none';
        }
    }
</script>

